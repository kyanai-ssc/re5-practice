<?php

use App\Model\Entity\UserAuthority;

$this->assign('title', '自動返信メール設定');
$this->assign('headerType', 'master');
$this->Breadcrumbs->add('自動返信メール設定');

?>

<?= $this->Flash->render('autoReplyMailsFinish') ?>
<?= $this->Flash->render('autoReplyMailsErrors') ?>

<section class="panel-show">
    <div class="panel-show-wrap">
        <?= $this->Form->create($searchForm, [
            'type' => 'post',
            'url' => [
                'controller' => 'AutoReplyMails',
                'action' => 'list',
                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
            ],
            'idPrefix' => 'tags_search',
            'novalidate' => true,
        ]) ?>
        <?= $this->element('Admin/Common/form/panel_show', [
            'panelTitle' => '自動返信メール検索',
        ]) ?>
        <div class="showWrap">
            <div class="panel-show-set">
                <fieldset>
                    <legend class="ttl-search mgb-20"></legend>
                    <table class="input-box">
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">メール種別</div>
                            </th>
                            <td>
                                <?= $this->Form->control('type', [
                                    'type' => 'select',
                                    'class' => ['select'],
                                    'options' => $valueOptions['type'],
                                    'empty' => '----',
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">送信対象</div>
                            </th>
                            <td>
                                <?= $this->Template->checkbox('user_authority_id', [
                                    'type' => 'multicheckbox',
                                    'options' => $valueOptions['userAuthorityId'],
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">カテゴリー</div>
                            </th>
                            <td>
                                <?= $this->Label->renderSelect([
                                    'type' => $this->Configure->read('Master.label.type.other'),
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">配信者名</div>
                            </th>
                            <td>
                                <?= $this->Form->control('from_mail_name', [
                                    'type' => 'text',
                                    'label' => false,
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">FROMアドレス</div>
                            </th>
                            <td>
                                <?= $this->Form->control('from_mail', [
                                    'type' => 'text',
                                    'label' => false,
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">件名</div>
                            </th>
                            <td>
                                <?= $this->Form->control('subject', [
                                    'type' => 'text',
                                    'label' => false,
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="field-input">
                            <th class="ttl-input">
                                <div class="ttl-input-wrap">本文</div>
                            </th>
                            <td>
                                <?= $this->Form->control('contents', [
                                    'type' => 'text',
                                    'label' => false,
                                ]) ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </div>
            <div class="btn-box mgt-20">
                <?= $this->Form->button('リセット', [
                    'type' => 'reset',
                    'class' => ['js_change_url', 'cmn-btn', 'is-reset'],
                    'data-url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'AutoReplyMails',
                        'action' => 'list',
                    ], ['escape' => false]),
                ]) ?>
                <?= $this->Form->button('検索', ['type' => 'submit', 'class' => 'cmn-btn is-blue']) ?>
            </div>
        </div>
        <?= $this->Form->end(); ?>
    </div><!-- .panel-search-wrap -->
</section><!-- .panel-search -->
<section class="search-list mgt-20">
    <aside class="search-parts d-flex mgt-20">
        <div class="search-parts-left">
            <?= $this->element('Admin/Common/search/page_counter') ?>
            <?= $this->Form->button('新規登録', [
                'type' => 'button',
                'class' => ['js_change_url', 'cmn-btn', 'is-newCreate',],
                'data-url' => $this->Url->build([
                    'prefix' => 'Admin',
                    'controller' => 'AutoReplyMails',
                    'action' => 'add',
                ], ['escape' => false]),
            ]) ?>
        </div><!-- .search-counter -->
        <div class="search-parts-center">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </div>
        <div class="search-parts-right">
            <ul class="d-flex">
                <li>
                    <?= $this->element('Admin/Common/search/limit', [
                        'options' => [
                            'data-url' => $this->Url->build([
                                'prefix' => 'Admin',
                                'controller' => 'AutoReplyMails',
                                'action' => 'list',
                                '?' => $this->Configure->read('Setting.searchInput.searchQuery'),
                            ], ['escape' => false]),
                        ],
                    ]) ?>
                </li>
            </ul>
        </div><!-- .search-parts-right -->
    </aside>
    <div class=search-parts-note>
        <div class="desc-wrap cmn-txt">
            <p>QRコードが表示できるメール種別は予約登録、予約変更、予約リマインダーとなります。テキストメールではQRコードは表示されませんのでご注意ください。</p>
        </div>
    </div>
    <?php if (count($autoReplyMails) > 0): ?>
    <div class="fixedTable-option">
        <aside class="fixedTable-arrow">
            <button id="left-button" type="button" class="fixedTable-scroll-btn is-prev cmn-btn"></button>
            <button id="right-button" type="button" class="fixedTable-scroll-btn is-next cmn-btn"></button>
        </aside><!-- .search-parts -->
    </div>
    <div class="fixedTable-wrap">
        <div class="fixedTable-in">
            <div class="fixedTableHead">
                <table class="cmn-table status-table">
                    <thead class="stickyTable">
                    <tr>
                        <th class="reId">
                            <div class="sort_wrap">
                                <span>ID</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>メール種別</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'type',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>送信対象</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'user_authority_id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>カテゴリー</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'label_id',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>配信者名</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'from_mail_name',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>FROMアドレス</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'from_mail',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>件名</span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'subject',
                                ]) ?>
                            </div>
                        </th>
                        <th>
                            <div class="sort_wrap">
                                <span>メール形式 </span>
                                <?= $this->element('Admin/Common/search/sort', [
                                    'key' => 'content_type',
                                ]) ?>
                            </div>
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <table class="cmn-table status-table fixedTableBody">
                <tbody>
                <?php foreach ($autoReplyMails as $autoReplyMail): ?>
                    <tr class="parent">
                        <td class="reId">
                            <?= h($autoReplyMail->id) ?>
                        </td>
                        <td>
                            <?= h($this->Configure->read('Master.autoReplyMail.type.' . $autoReplyMail->type)) ?>
                        </td>
                        <td>
                            <?php if ($autoReplyMail->user_authority_id === null) : ?>
                                <?= h($this->Configure->read('Master.userAuthority.selectAll.' . UserAuthority::SELECT_ALL)) ?>
                            <?php elseif (isset($valueOptions['userAuthorityId'][$autoReplyMail->user_authority_id])) : ?>
                                <?= h($valueOptions['userAuthorityId'][$autoReplyMail->user_authority_id]) ?>
                            <?php else: ?>
                                該当権限が削除されています。
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($autoReplyMail->label_id !== null): ?>
                                <?= $this->Text->truncate($autoReplyMail->label->name, 30, ['ellipsis' => '...', 'tooltip' => true]) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($autoReplyMail->from_mail_name, 30, ['tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($autoReplyMail->from_mail, 30, ['tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?= $this->Text->truncate($autoReplyMail->subject, 30, ['tooltip' => true, 'escape' => true]) ?>
                        </td>
                        <td>
                            <?= h($this->Configure->read('Master.common.mailFormatName.' . $autoReplyMail->content_type)) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <table class="fixedTableLeft cmn-table status-table js_check_popup_table">
            <thead>
            <tr>
                <th class="tool fixedElm"><span>操作</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($autoReplyMails as $autoReplyMail): ?>
            <tr>
                <td class="tool">
                    <ul>
                        <li>
                            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_editor"></use></svg>', [
                                'type' => 'button',
                                'title' => '編集',
                                'class' => ['js_change_url', 'btn-tool', 'is-editor'],
                                'data-url' => $this->Url->build([
                                    'prefix' => 'Admin',
                                    'controller' => 'AutoReplyMails',
                                    'action' => 'edit',
                                    'id' => $autoReplyMail->id,
                                ], ['escape' => false]),
                                'escapeTitle' => false,
                            ]) ?>
                        </li>
                        <li>
                            <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_copy"></use></svg>', [
                                'type' => 'button',
                                'title' => '複製',
                                'class' => ['js_change_url', 'btn-tool', 'is-copy'],
                                'data-url' => $this->Url->build([
                                    'prefix' => 'Admin',
                                    'controller' => 'AutoReplyMails',
                                    'action' => 'copy',
                                    'id' => $autoReplyMail->id,
                                ], ['escape' => false]),
                                'escapeTitle' => false,
                            ]) ?>
                        </li>
                        <?php if ($autoReplyMail->canDelete()) : ?>
                            <li>
                                <?= $this->Form->button('<svg class="icon"><use xlink:href="#icon_tool_delete"/></svg>', [
                                    'type' => 'button',
                                    'class' => ['js_post_confirm', 'btn-tool', 'is-delete'],
                                    'title' => '削除',
                                    'data-confirm-message' => '自動返信メールの削除をおこなってよろしいですか？',
                                    'data-confirm-title' => '自動返信メールの削除',
                                    'data-confirm-html' => h('削除したデータの復旧はできません。'),
                                    'data-url' => $this->Url->build([
                                        'prefix' => 'Admin',
                                        'controller' => 'AutoReplyMails',
                                        'action' => 'delete',
                                        'id' => $autoReplyMail->id,
                                    ], ['escape' => false]),
                                    'escapeTitle' => false,
                                ]) ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </td>
                <?php endforeach; ?>
            </tbody>
        </table>
        <aside class="search-parts mgt-20">
            <?= $this->element('Admin/Common/search/paginator') ?>
        </aside>
        <?php else: ?>
            <?= $this->element('Admin/Common/search/no_result') ?>
        <?php endif; ?>
    </div>
</section>
