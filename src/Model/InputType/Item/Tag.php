<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\ListOutputInterface;
use App\Model\InputType\Item\Type\ListOutputTrait;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Model\InputType\Item\Type\SearchDisplayTrait;
use App\Utility\ArrayUtility;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * Tag class.
 */
class Tag extends AbstractInputTypeItem implements ListOutputInterface, MailOutputInterface, SearchDisplayInterface
{
    use ListOutputTrait;
    use MailOutputTrait;
    use SearchDisplayTrait;

    /**
     * @var string
     */
    protected $tableName = 'event_tags';

    /**
     * @var string
     */
    protected $columnName = 'tag_id';

    /**
     * @var array|null
     */
    protected $valueOptions = null;

    /**
     * @var array|null
     */
    protected $tagValueOptions = null;

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        if (is_null($options)) {
            return null;
        }

        $event = Hash::get($options, 'event');
        if (!isset($event)) {
            return null;
        }
        $eventTags = $event->get('event_tags');
        if (!isset($eventTags)) {
            return null;
        }

        $tagIds = [];
        foreach ($eventTags as $eventTag) {
            $tagIds[$eventTag->get('tag_id')] = $eventTag->get('tag_id');
        }

        $tagGroups = [];
        foreach ($this->getValueOptions() as $tagGroupId => $tagGroup) {
            $tagGroup['tag'] = array_intersect_key($tagGroup['tag'], $tagIds);
            if (!empty($tagGroup['tag'])) {
                $tagGroups[$tagGroupId] = $tagGroup;
            }
        }

        if (empty($tagGroups)) {
            return null;
        }

        return $tagGroups;
    }

    /**
     * @inheritDoc
     */
    public function getListValue(?array $options = null)
    {
        return $this->getDetailValue($options);
    }

    /**
     * @inheritDoc
     */
    public function buildSearchValidator(Validator $validator)
    {
        $tagValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->add($this->getSearchInputKey(), [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'tagGroups' => [
                    'rule' => function ($value) {
                        $tagGroups = $this->getValueOptions();
                        foreach ((array)$value as $tagGroupId => $tagIds) {
                            $values = Hash::get($tagGroups, $tagGroupId);
                            if (is_array($tagIds) && is_array($values) && !empty($values['tag'])) {
                                foreach ($tagIds as $tagId) {
                                    if (
                                        !is_scalar($tagId)
                                        || !ArrayUtility::inArray($tagId, array_keys($values['tag']))
                                    ) {
                                        return false;
                                    }
                                }
                            }
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchQuery(Query $query, array $inputs)
    {
        $eventTagsTable = $this->getTableLocator()->get('EventTags');

        $values = Hash::get($inputs, $this->getSearchInputKey());
        if (is_scalar($values) && ((string)$values !== '') || is_array($values) && !empty($values)) {
            $tagIds = [];
            foreach ((array)$values as $value) {
                if (is_scalar($value) && ((string)$value !== '') || is_array($value) && !empty($value)) {
                    foreach ((array)$value as $tagId) {
                        $tagIds[] = $tagId;
                    }
                }
            }
            if (count($tagIds) > 0) {
                $eventTagsQuery = $eventTagsTable->find();
                $eventTagsQuery->select(['EventTags.event_id']);
                $eventTagsQuery->where(['EventTags.tag_id IN' => $tagIds]);
                $query->where([
                    'Events.id IN' => $eventTagsQuery,
                ]);
            }
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'event_tag';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        $eventTags = Hash::get($data, 'event.event_tags');
        if (!isset($eventTags)) {
            return null;
        }

        $tagIds = [];
        foreach ($eventTags as $eventTag) {
            $tagIds[$eventTag['tag_id']] = $eventTag['tag_id'];
        }

        $tagGroups = [];
        foreach ($this->getValueOptions() as $tagGroupId => $tagGroup) {
            $tags = array_intersect_key($tagGroup['tag'], $tagIds);
            if (!empty($tags)) {
                $tagGroups[$tagGroupId] = $tagGroup['name'] . '：' . implode('、', $tags);
            }
        }

        if (empty($tagGroups)) {
            return null;
        }

        return implode("\n", $tagGroups);
    }

    /**
     * 選択肢を取得
     *
     * @return array 選択肢
     */
    public function getValueOptions()
    {
        if (!isset($this->valueOptions)) {
            /** @var \App\Model\Table\TagGroupsTable $tagGroupsTable */
            $tagGroupsTable = $this->getTableLocator()->get('TagGroups');

            $this->valueOptions = $tagGroupsTable->getTagsList();
        }

        return $this->valueOptions;
    }
}
