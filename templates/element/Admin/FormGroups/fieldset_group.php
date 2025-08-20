<div class="js_form_groups_container_<?= h($formGroupIndex) ?> mgb-10">
    <div class="ttl-panel-show">
        <span class="handle">
            <svg class="icon is-drag"><use xlink:href="#icon_drag"/></svg>
        </span>
        <span class="ttl-s">
            <?php if (!is_null($formGroup)) : ?><?= h($formGroup->get('name')) ?><?php endif; ?>
        </span>
        <button type="button" class="showBtn"></button>
        <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
            'type' => 'button',
            'title' => '削除',
            'class' => ['btn-input', 'is-delete', 'formM', 'tooltip', 'js_delete_form_group'],
            'data-form-type' => $formType,
            'data-selector' => '.js_form_groups_container_' . $formGroupIndex,
            'data-context' => '.js_form_groups_container',
            'data-confirm-title' => 'グループの削除',
            'data-confirm-message' => 'ご登録いただいているデータも削除されますが、削除してもよろしいでしょうか？',
            'data-confirm-html' => h('削除したデータの復旧はできません。'),
            'escapeTitle' => false,
        ]) ?>
    </div>
    <div class="showWrap">
        <div class="panel-show-set">
            <fieldset class="mgt-20">
                <table class="input-box sortable">
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                グループ名<?= $this->Template->isRequire('form_groups.' . $formGroupIndex . '.name') ?>
                            </div>
                        </th>
                        <td>
                            <input type="hidden" class="js_form_groups_index" value="<?= h($formGroupIndex) ?>"/>
                            <?= $this->Form->hidden('form_groups.' . $formGroupIndex . '.id') ?>
                            <?= $this->Form->control('form_groups.' . $formGroupIndex . '.name', [
                                'type' => 'text',
                            ]) ?>
                        </td>
                    </tr>
                    <tr class="field-input">
                        <th class="ttl-input">
                            <div class="ttl-input-wrap">
                                グループ名表示<?= $this->Template->isRequire('form_groups.' . $formGroupIndex . '.name_display_flg') ?>
                            </div>
                        </th>
                        <td>
                            <?= $this->Template->radio('form_groups.' . $formGroupIndex . '.name_display_flg', [
                                'type' => 'radio',
                                'options' => $valueOptions['nameDisplayFlg'],
                                'default' => '',
                            ]) ?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div><!-- .panel-show-set -->
        <div class="panel-show-set">
            <fieldset>
                <table class="cmn-table form-table sortable">
                    <thead>
                    <tr>
                        <th class="w70">
                            <span>&nbsp;</span>
                        </th>
                        <th class="w-100 tool">
                            <span>編集</span>
                        </th>
                        <th>
                            <span>項目名</span>
                        </th>
                        <th class="w-100">
                            <span>必須</span>
                        </th>
                        <th>
                            <span>入力タイプ</span>
                        </th>
                        <?php if ($this->ArrayUtility->inArray($formType, $this->Configure->read('Master.form.canReservationDisplayFormType'))): ?>
                            <th class="w-100">
                                <span>予約画面表示</span>
                            </th>
                        <?php endif; ?>
                        <th class="w-180">
                            <span>入力チェック</span>
                        </th>
                        <th class="w-180">
                            <span>入力変換</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="js_form_items_container_<?= h($formGroupIndex) ?> js_sortable js_sortable_items">
                    <?php if (isset($formGroup) && is_array($formGroup->get('form_items'))): ?>
                        <?php foreach ($formGroup->get('form_items') as $formItemIndex => $formItem): ?>
                            <?= $this->element('Admin/FormGroups/list_item', [
                                'formType' => $formType,
                                'formItem' => $formItem,
                                'formGroupIndex' => $formGroupIndex,
                                'formItemIndex' => $formItemIndex,
                            ]) ?>
                        <?php endforeach; ?>
                        <tr class="ui-state-disabled h-30 hidden"></tr>
                    <?php else: ?>
                        <tr class="ui-state-disabled h-30 hidden"></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <div>
                    <?= $this->FormError->errorWithoutNested('form_groups.' . $formGroupIndex . '.form_items') ?>
                    <?= $this->Form->button('項目追加', [
                        'type' => 'button',
                        'class' => ['js_add_form_item', 'cmn-btn', 'is-formAdd'],
                        'data-form-type' => $formType,
                        'data-form-group-index' => $formGroupIndex,
                    ]) ?>
                </div>
            </fieldset>
        </div>
    </div>
</div>
