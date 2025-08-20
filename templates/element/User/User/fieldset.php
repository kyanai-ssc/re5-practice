<?= $this->element('User/Common/fieldset/input_items_fieldset', [
    'formGroups' => $userForm->getUserFormGroups(),
    'options' => [
        'user' => $userForm->getUserEntity(),
        'mode' => $mode,
    ],
]) ?>
<?php if ($userForm->requiredUserTerms()): ?>
    <h3 class="ttl-sec mgt-40"><?= $this->Tr->h('user/userTerms') ?></h3>
    <fieldset class="input-info">
        <div class="userPolicy wysiwyg-area">
            <?= $userForm->getUserTerms() ?>
        </div>
        <p class="cmn-txt tac mgt-20">
            <?= $this->Template->checkbox('user_terms', [
                'type' => 'checkbox',
                'label' => [
                    'text' => $this->Tr->t('user/agreeUserTerms'),
                    'class' => ['cmn-check'],
                ],
            ]) ?>
        </p>
    </fieldset>
<?php endif; ?>
