<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\FormPatterns\SearchForm;
use App\Locale\Message;
use App\Model\Entity\FormPatternDisplayType;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * FormPatterns Controller
 */
class FormPatternsController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'delete',
        ]);

        /** @var \App\Model\Table\FormPatternsTable $formPatternsTable */
        $formPatternsTable = $this->fetchTable('FormPatterns');

        if ($formPatternsTable->setFormType($this->getRequest()->getParam('formType'))) {
            throw new NotFoundException();
        }

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'FormPatterns',
            'action' => 'list',
        ], 301);
    }

    /**
     * List method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function list()
    {
        /** @var \App\Model\Table\FormPatternsTable $formPatternsTable */
        $formPatternsTable = $this->fetchTable('FormPatterns');

        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('formPatterns.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('formPatterns.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('formPatterns.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $formPatterns = $this->Pagination->paginate($this->fetchTable('FormPatterns'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('formPatterns.list.search', $searchData);

        // ビュー変数
        $this->set([
            'formType' => $formPatternsTable->getFormType(),
            'formTypeName' => $formPatternsTable->getFormTypeName(),
            'formTypeUrl' => $formPatternsTable->getFormTypeUrl(),
            'formPatterns' => $formPatterns,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
        ]);
    }

    /**
     * Add method
     *
     * @param int $id ID コピー用
     * @return \Cake\Http\Response|null|void
     */
    public function add($id = null)
    {
        /** @var \App\Model\Table\FormGroupsTable $formGroupsTable */
        $formGroupsTable = $this->fetchTable('FormGroups');
        /** @var \App\Model\Table\FormPatternsTable $formPatternsTable */
        $formPatternsTable = $this->fetchTable('FormPatterns');

        $options = $formPatternsTable->getFindOptions([]);
        $options['inputs'][Configure::readOrFail('Master.formPattern.selectGroupAll.key')] = true;
        $formGroups = $formGroupsTable->getFormGroupList($options);
        $selectGroupId = $this->getRequest()->getQuery('group');
        if (empty($selectGroupId)) {
            $selectGroupId = $this->getRequest()->getData('form_group_id');
        }
        if (!empty($selectGroupId) && is_string($selectGroupId)) {
            if ($selectGroupId != Configure::readOrFail('Master.formPattern.selectGroupAll.key')) {
                if (!array_key_exists($selectGroupId, $formGroups)) {
                    throw new BadRequestException(Message::ERROR_INVALID_URL);
                }
                $options['inputs']['form_group_id'] = (int)$selectGroupId;
            }
            $forms = $this->fetchTable('FormItems')->find('Items', $options);
        } else {
            $forms = [];
        }

        // バリデート生成
        $formPatternsTable->createValidatorForPatterns($forms);

        $optionChecked = [];
        if ($this->getRequest()->is('post')) {
            // JSONデコード
            $data = $this->getRequest()->getData();
            if (empty($data['formJson'])) {
                throw new BadRequestException();
            }
            $post = $this->formPatternJsonDecode($data['formJson']);
            // 入力チェック
            $formPattern = $formPatternsTable->newEntity((array)$post, [
                'associated' => [
                    'FormPatternDisplayTypes' => [],
                    'FormPatternOptions' => [],
                ],
            ]);

            // 表示しているオプションを保持
            $options = [];
            $targetOptions = [];
            if ($forms instanceof Query) {
                $options = array_column(array_column($forms->toArray(), 'form_item_option_group'), 'form_item_options');
            }
            if (!empty($options)) {
                foreach ($options as $option) {
                    foreach ($option as $data) {
                        $targetOptions[] = $data['id'];
                    }
                }
            }

            // データ保存
            if (
                $formPatternsTable->save(
                    $formPattern,
                    [
                    'saveOperation' => [
                        'controller' => $formPatternsTable->getFormTypeUrl(),
                        'action' => $this->getRequest()->getParam('action'),
                    ],
                    'targetOptions' => $targetOptions,
                    ]
                )
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'formPatternsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'FormPatterns',
                    'action' => 'list',
                    '_name' => $formPatternsTable->getFormTypeUrl(),
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set(
                    (string)__(Message::INVALID_INPUT),
                    [
                        'key' => 'formPatternsErrors',
                        'element' => 'error',
                    ]
                );
            }
        } else {
            if (!is_null($id)) {
                // エンティティー生成
                $forms = $this->fetchTable('FormItems')->find('Items', $formPatternsTable->getFindOptions((array)$id));
                $formPattern = $formPatternsTable->get($id, [
                    'finder' => 'edit',
                    'formItem' => $forms,
                ]);
                $formPattern->setNew(true);
                $formPattern->unset('id');
                $optionChecked = $formPatternsTable->getOptionChecked($formPattern);
            } else {
                // エンティティ生成
                $formPattern = $formPatternsTable->newEntity([], [
                    'validate' => false,
                    'associated' => [
                        'FormPatternOptions' => [
                            'validate' => false,
                        ],
                    ],
                ]);
            }
        }

        // ビュー変数
        $this->set([
            'formType' => $formPatternsTable->getFormType(),
            'formTypeName' => $formPatternsTable->getFormTypeName(),
            'formTypeUrl' => $formPatternsTable->getFormTypeUrl(),
            'formPattern' => $formPattern,
            'forms' => $forms,
            'formGroups' => $formGroups,
            'selectGroupId' => $selectGroupId,
            'optionChecked' => $optionChecked,
            'valueOptions' => $formPatternsTable->getFieldValueOptions(),
        ]);
    }

    /**
     * Edit method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        /** @var \App\Model\Table\FormGroupsTable $formGroupsTable */
        $formGroupsTable = $this->fetchTable('FormGroups');
        /** @var \App\Model\Table\FormPatternsTable $formPatternsTable */
        $formPatternsTable = $this->fetchTable('FormPatterns');

        $options = $formPatternsTable->getFindOptions((array)$id);
        $options['inputs'][Configure::readOrFail('Master.formPattern.selectGroupAll.key')] = true;
        $formGroups = $formGroupsTable->getFormGroupList($options);
        $selectGroupId = $this->getRequest()->getQuery('group');
        if (empty($selectGroupId)) {
            $selectGroupId = $this->getRequest()->getData('form_group_id');
        }
        if (!empty($selectGroupId) && is_string($selectGroupId)) {
            if ($selectGroupId != Configure::readOrFail('Master.formPattern.selectGroupAll.key')) {
                if (!array_key_exists($selectGroupId, $formGroups)) {
                    throw new BadRequestException(Message::ERROR_INVALID_URL);
                }
                $options['inputs']['form_group_id'] = (int)$selectGroupId;
            }
            $forms = $this->fetchTable('FormItems')->find('Items', $options);
        } else {
            $forms = [];
        }
        $formPattern = $formPatternsTable->get($id, [
            'finder' => 'edit',
            'formItem' => $forms,
        ]);

        // バリデート生成
        $formPatternsTable->createValidatorForPatterns($forms);

        $optionChecked = [];
        if ($this->getRequest()->is('post')) {
            // JSONデコード
            $data = $this->getRequest()->getData();
            if (empty($data['formJson'])) {
                throw new BadRequestException();
            }
            $post = $this->formPatternJsonDecode($data['formJson']);
            // 入力チェック
            $formPatternsTable->patchEntity($formPattern, (array)$post, [
                'associated' => [
                    'FormPatternDisplayTypes' => [],
                    'FormPatternOptions' => [],
                ],
            ]);

            // 表示しているオプションを保持
            $options = [];
            $targetOptions = [];
            if ($forms instanceof Query) {
                $options = array_column(array_column($forms->toArray(), 'form_item_option_group'), 'form_item_options');
            }
            if (!empty($options)) {
                foreach ($options as $option) {
                    foreach ($option as $data) {
                        $targetOptions[] = $data['id'];
                    }
                }
            }

            if (
                $formPatternsTable->save($formPattern, [
                'saveOperation' => [
                    'controller' => $formPatternsTable->getFormTypeUrl(),
                    'action' => $this->getRequest()->getParam('action'),
                ],
                'targetOptions' => $targetOptions,
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'formPatternsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'FormPatterns',
                    'action' => 'list',
                    '_name' => $formPatternsTable->getFormTypeUrl(),
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set(
                    (string)__(Message::INVALID_INPUT),
                    [
                        'key' => 'formPatternsErrors',
                        'element' => 'error',
                    ]
                );
            }
        }
        $optionChecked = $formPatternsTable->getOptionChecked($formPattern);

        // ビュー変数
        $this->set([
            'formType' => $formPatternsTable->getFormType(),
            'formTypeName' => $formPatternsTable->getFormTypeName(),
            'formTypeUrl' => $formPatternsTable->getFormTypeUrl(),
            'formPattern' => $formPattern,
            'forms' => $forms,
            'formGroups' => $formGroups,
            'selectGroupId' => $selectGroupId,
            'optionChecked' => $optionChecked,
            'valueOptions' => $formPatternsTable->getFieldValueOptions(),
        ]);
    }

    /**
     * togetherEdit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function togetherEdit()
    {
        /** @var \App\Model\Table\FormGroupsTable $formGroupsTable */
        $formGroupsTable = $this->fetchTable('FormGroups');
        /** @var \App\Model\Table\FormPatternsTable $formPatternsTable */
        $formPatternsTable = $this->fetchTable('FormPatterns');

        $optionChecked = [];

        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition() && !$this->SearchInput->shouldSaveExec()) {
            $this->getRequest()->getSession()->delete('formPatterns.togetherEdit.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('formPatterns.togetherEdit.search')
        );

        if ($this->SearchInput->shouldSaveExec()) {
            $searchInputs['name'] = $this->getRequest()->getSession()->read('formPatterns.togetherEdit.search.name');
        }

        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('formPatterns.togetherEdit.search')
            );
        }

        // データ取得
        $formPatterns = $this->Pagination->paginate($this->fetchTable('FormPatterns'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                    'together' => true,
                ],
            ],
            'maxLimit' => Configure::readOrFail('Master.formPattern.togetherEdit.updateLimit'),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('formPatterns.togetherEdit.search', $searchData);

        $options = $formPatternsTable->getFindOptions(Hash::combine($formPatterns->toarray(), '{n}.id', '{n}.id'));
        $options['inputs'][Configure::readOrFail('Master.formPattern.selectGroupAll.key')] = true;
        $formGroups = $formGroupsTable->getFormGroupList($options);
        $selectGroupId = null;
        if (!empty($this->getRequest()->getQuery('group'))) {
            $selectGroupId = $this->getRequest()->getQuery('group');
        } elseif (!empty($this->getRequest()->getData('form_group_id'))) {
            $selectGroupId = $this->getRequest()->getData('form_group_id');
        } elseif (!empty($this->getRequest()->getSession()->read('formPatterns.togetherEdit.search.group'))) {
            $selectGroupId = $this->getRequest()->getSession()->read('formPatterns.togetherEdit.search.group');
        }
        if (empty($selectGroupId)) {
            $selectGroupId = $this->getRequest()->getData('form_group_id');
        }
        if (!empty($selectGroupId) && is_string($selectGroupId)) {
            if ($selectGroupId != Configure::readOrFail('Master.formPattern.selectGroupAll.key')) {
                if (!array_key_exists($selectGroupId, $formGroups)) {
                    throw new BadRequestException(Message::ERROR_INVALID_URL);
                }
                $this->getRequest()->getSession()->write('formPatterns.togetherEdit.search.group', $selectGroupId);
                $options['inputs']['form_group_id'] = (int)$selectGroupId;
            }
        }

        $forms = $this->fetchTable('FormItems')->find('Items', $options);
        $formPatternsTable->createValidatorForPatterns($forms, true);

        $formPatterns = $formPatternsTable->createEntities($formPatterns->toArray(), $forms);

        if ($this->SearchInput->shouldSaveExec() && $this->getRequest()->is('post')) {
            // 入力値取得
            $data = (array)$this->getRequest()->getData();
            if (empty($data['formJson'])) {
                throw new BadRequestException();
            }
            // JSONデコード
            $formPatternInputs = $this->formPatternTogetherJsonDecode($data['formJson']);

            $formPatterns = $formPatternsTable->patchEntities(
                $formPatterns,
                $formPatternInputs['form_patterns']
            );

            // 表示しているオプションを保持
            $options = [];
            $targetOptions = [];
            $options = array_column(array_column($forms->toArray(), 'form_item_option_group'), 'form_item_options');
            if (!empty($options)) {
                foreach ($options as $option) {
                    foreach ($option as $data) {
                        $targetOptions[] = $data['id'];
                    }
                }
            }

            if (
                $formPatternsTable->saveMany($formPatterns, [
                'saveOperation' => [
                    'controller' => $formPatternsTable->getFormTypeUrl(),
                    'action' => $this->getRequest()->getParam('action'),
                ],
                'targetOptions' => $targetOptions,
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'formPatternsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'FormPatterns',
                    'action' => 'togetherEdit',
                    '_name' => $formPatternsTable->getFormTypeUrl(),
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set(
                    (string)__(Message::INVALID_INPUT),
                    [
                        'key' => 'formPatternsErrors',
                        'element' => 'error',
                    ]
                );
            }
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));
        $optionChecked = [];
        foreach ($formPatterns as $key => $formPattern) {
            $optionChecked[$key] = $formPatternsTable->getOptionChecked($formPattern);
        }

        // ビュー変数
        $this->set([
            'formType' => $formPatternsTable->getFormType(),
            'formTypeName' => $formPatternsTable->getFormTypeName(),
            'formTypeUrl' => $formPatternsTable->getFormTypeUrl(),
            'forms' => $forms,
            'formPatterns' => $formPatterns,
            'formGroups' => $formGroups,
            'selectGroupId' => $selectGroupId,
            'searchForm' => $searchForm,
            'optionChecked' => $optionChecked,
            'valueOptions' => $formPatternsTable->getFieldValueOptions(),
        ]);
    }

    /**
     * Copy method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function copy($id = null)
    {
        $this->add($id);

        $this->render('add');
    }

    /**
     * Delete method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\FormPatternsTable $formPatternsTable */
        $formPatternsTable = $this->fetchTable('FormPatterns');

        $formPattern = $formPatternsTable->get($id, [
            'finder' => 'delete',
        ]);

        if (!$formPattern->canDelete()) {
            throw new NotFoundException();
        }

        // データ削除
        $formPatternsTable->deleteOrFail($formPattern, [
            'saveOperation' => [
                'controller' => $formPatternsTable->getFormTypeUrl(),
                'action' => $this->getRequest()->getParam('action'),
            ],
            'associated' => [
                'FormPatternDisplayTypes',
                'FormPatternOptions',
            ],
        ]);
        // 完了メッセージ
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'formPatternsFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'FormPatterns',
            'action' => 'list',
            '_name' => $formPatternsTable->getFormTypeUrl(),
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }

    /**
     * 表示パターンのPOST値JSONデコード
     *
     * @param string|null $jsonString 表示パターンのPOST値
     * @return array
     */
    protected function formPatternJsonDecode($jsonString)
    {
        $result = [];

        if (empty($jsonString)) {
            return $result;
        }
        if (is_string($jsonString)) {
            $formJson = json_decode($jsonString, true);
            $match = [];
            $flgOff = (string)FormPatternDisplayType::APP_DISPLAY_FLG_OFF;
            foreach ($formJson as $jsonData) {
                if ($jsonData['name'] === 'name' || $jsonData['name'] === 'remark') {
                    $result[$jsonData['name']] = $jsonData['value'];
                }
                // 表示パターンの選択情報を配列化
                if (preg_match('/^form_pattern_display_types\[(\d+)\]/', $jsonData['name'], $match)) {
                    if (empty($result['form_pattern_display_types'][$match[1]]['app_display_flg'])) {
                        // 「アプリに表示」を未チェックで初期化
                        $result['form_pattern_display_types'][$match[1]]['app_display_flg'] = $flgOff;
                    }
                }
                if (preg_match('/^form_pattern_display_types\[(\d+)\]\[([^\]]+)\]$/', $jsonData['name'], $match)) {
                    $result['form_pattern_display_types'][$match[1]][$match[2]] = $jsonData['value'];
                }
                // オプションの選択情報を配列化
                if (preg_match('/^form_pattern_options\[(\d+)\]\[([^\]]+)\]$/', $jsonData['name'], $match)) {
                    $result['form_pattern_options'][$match[1]][$match[2]] = $jsonData['value'];
                }
            }
        }

        return $result;
    }

    /**
     * 一括編集の表示パターンのPOST値JSONデコード
     *
     * @param string|null $jsonString 一括編集の表示パターンのPOST値
     * @return array
     */
    protected function formPatternTogetherJsonDecode($jsonString)
    {
        $result = [];

        if (empty($jsonString)) {
            return $result;
        }
        if (is_string($jsonString)) {
            $formJson = json_decode($jsonString, true);
            foreach ($formJson as $key => $jsonData) {
                // form_patterns配列
                $formName = 'form_patterns';
                $patternItem = '/^' . $formName . '\[(\d+)\]\[([^\]]+)\]\[(\d+)\]\[([^\]]+)\]$/';
                $match = [];
                $flgOff = (string)FormPatternDisplayType::APP_DISPLAY_FLG_OFF;
                if (preg_match('/^' . $formName . '\[(\d+)\]\[id\]$/', $jsonData['name'], $match)) {
                    $result[$formName][$match[1]]['id'] = $jsonData['value'];
                } elseif (preg_match($patternItem, $jsonData['name'], $match)) {
                    if ($match[2] === 'form_pattern_display_types') {
                        if (empty($result[$formName][$match[1]][$match[2]][$match[3]]['app_display_flg'])) {
                            // 「アプリに表示」を未チェックで初期化
                            $result[$formName][$match[1]][$match[2]][$match[3]]['app_display_flg'] = $flgOff;
                        }
                        $result[$formName][$match[1]][$match[2]][$match[3]][$match[4]] = $jsonData['value'];
                    } elseif ($match[2] === 'form_pattern_options') {
                        $result[$formName][$match[1]][$match[2]][$match[3]][$match[4]] = $jsonData['value'];
                    }
                }
            }
        }

        return $result;
    }
}
