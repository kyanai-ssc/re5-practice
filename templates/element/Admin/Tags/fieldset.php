<?php $this->Form->unlockField('tags'); ?>

<div class="form-input-set">
    <fieldset>
        <table class="input-box">
            <tbody>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">グループ名<?= $this->Template->isRequire('name') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('name', [
                        'type' => 'text',
                        'label' => false,
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">表示順<?= $this->Template->isRequire('sort_no') ?></div>
                </th>
                <td>
                    <?= $this->Form->control('sort_no', [
                        'type' => 'text',
                        'label' => false,
                        'class' => ['textbox_w70']
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">公開設定<?= $this->Template->isRequire('public_flg') ?></div>
                </th>
                <td>
                    <?= $this->Template->radio('public_flg', [
                        'type' => 'radio',
                        'label' => false,
                        'class' => ['cmn-radio'],
                        'options' => $valueOptions['publicFlg'],
                        'default' => \App\Model\Entity\TagGroup::PUBLIC_FLG_ON
                    ]) ?>
                </td>
            </tr>
            <tr class="field-input">
                <th class="ttl-input">
                    <div class="ttl-input-wrap">キーワード設定<?= $this->Template->isRequire('tags') ?></div>
                </th>
                <td>
                    <div>
                        <?= $this->FormError->errorWithoutNested('tags'); ?>
                        <table class="pliceInput-detail">
                            <thead>
                            <tr>
                                <th>キーワード名</th>
                                <th>表示順</th>
                                <th>公開設定</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="js_tags_container">
                            <?php if (isset($tag->tags)): ?>
                                <?php foreach ($tag->tags as $tagIndex => $tagData): ?>
                                    <?= $this->element('Admin/Tags/fieldset_tag', [
                                        'tag' => $tag,
                                        'tagIndex' => $tagIndex,
                                        'tagData' => $tagData,
                                    ]) ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        <?= $this->Form->button('キーワード追加', [
                            'type' => 'button',
                            'class' => ['js_add_input', 'cmn-btn', 'is-addCell', 'mgt-20'],
                            'data-container' => '.js_tags_container',
                            'data-html' => $this->Template->addInputTemplate('Admin/Tags/fieldset_tag', [
                                'tag' => $tag,
                                'tagIndex' => '%INDEX%',
                                'tag' => null,
                            ]),
                            'data-index-element' => '.js_tags_index',
                            'data-index-replace' => '%INDEX%',
                        ]) ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
