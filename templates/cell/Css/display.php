@charset "UTF-8";
<?php if (!empty($site_theme['custom_color_4'])) : ?>
body {
background-color: <?= h($site_theme['custom_color_4']) ?> !important;
}

.list__line_wrap:nth-child(even),
.detail-name:nth-child(even) {
background-color: <?= h($site_theme['custom_color_4']) ?> !important;
}
<?php endif; ?>
<?php if (!empty($site_theme['custom_color_5'])) : ?>

.cmn-header,  .schedule-header,  .contents-area,  .cmn-footer, .ui-widget-content {
background: <?= h($site_theme['custom_color_5']) ?> !important;
}

@media (max-width: 767px) {
.btn-group,
.box-nav .cmn-sp-menu .nav-icon li {
background: <?= h($site_theme['custom_color_5']) ?> !important;
}
}
<?php endif; ?>

<?php if (!empty($site_theme['custom_color_1'])) : ?>
.cmn-header,  .cmn-footer {
border-color: <?= h($site_theme['custom_color_1']) ?> !important;
}

.totopBtn, .ttl-sec::before {
background-color: <?= h($site_theme['custom_color_1']) ?> !important;
}
<?php endif; ?>

<?php if (!empty($site_theme['custom_color_4'])) : ?>
.cmn-sp-menu,
.box-nav .cmn-sp-menu {
background-color: <?= h($site_theme['custom_color_4']) ?> !important;
}
<?php endif; ?>

<?php if (!empty($site_theme['custom_color_2'])) : ?>
.link-txt, .cmn-pager a, .unReserve a, .pageTitle, .wysiwyg-area a, .list-breadC a {
color: <?= h($site_theme['custom_color_2']) ?> !important;
}

.cmn-btn.is-blue, .cmn-btn.is-green,  input[type="button"].cmn-btn.is-green, .ui-tooltip, .arrow:after {
background-color: <?= h($site_theme['custom_color_2']) ?> !important;
}

.icon svg,
span.current-day .icon .icon-calendar,
.box-breadC .icon-home,
.help-btn .icon-help,
.current-day .icon .icon-calendar {
fill: <?= h($site_theme['custom_color_2']) ?> !important;
}

.btn-menu {
border-color: <?= h($site_theme['custom_color_2']) ?> !important;
}

.viewChange a:hover {
color: <?= h($site_theme['custom_color_2']) ?> !important;
}
.viewChange a:hover svg {
fill: <?= h($site_theme['custom_color_2']) ?> !important;
}

@media (max-width: 767px) {
.box-nav .cmn-sp-menu .sp-midle-header .icon-help + span,
.box-nav .cmn-sp-menu .nav-icon a span {
color: <?= h($site_theme['custom_color_2']) ?> !important;
}
}

.viewChange .select {
border-color: <?= h($site_theme['custom_color_2']) ?> !important;
}

.viewChange .select a svg.icon-scNav {
fill: <?= h($site_theme['custom_color_2']) ?> !important;
}

.viewChange .select a {
color:  <?= h($site_theme['custom_color_2']) ?> !important;
}

.viewChange .select a span {
background-color:  <?= h($site_theme['custom_color_2']) ?> !important;
}

label.cmn-radio::before {
background: <?= h($site_theme['custom_color_2']) ?> !important;
}

input.cmn-check:checked + .cmn-check::after {
background: <?= h($site_theme['custom_color_2']) ?> !important;
}

input.radio-label:checked + .retrieval-btn.is-label {
color: <?= $site_theme['custom_color_2'] ?> !important;
border-color: <?= h($site_theme['custom_color_2']) ?> !important;
}

input.radio-label:checked + .retrieval-btn.is-label::after {
border-color: <?= h($site_theme['custom_color_2']) ?> !important;
}

input.check-tag:checked + .retrieval-btn.is-tag {
color: <?= h($site_theme['custom_color_2']) ?> !important;
border-color: <?= h($site_theme['custom_color_2']) ?> !important;
}

input.check-tag:checked + .retrieval-btn.is-tag::after {
background: <?= h($site_theme['custom_color_2']) ?> !important;
}

#calender_list #timeTableHead th a,
#calender_list table.timeTable.time thead tr:nth-child(2) th a {
color: <?= h($site_theme['custom_color_2']) ?> !important;
}

.flowInput.is-current::after,  .flowConfirm.is-current::after,  .flowComplete.is-current::after {
background-color: <?= h($site_theme['custom_color_2']) ?> !important;
}

.flowInput.is-current .flowNum,  .flowConfirm.is-current .flowNum,  .flowComplete.is-current .flowNum {
background-color: <?= h($site_theme['custom_color_2']) ?> !important;
}

.flowInput.is-current p,  .flowConfirm.is-current p,  .flowComplete.is-current p {
color: <?= h($site_theme['custom_color_2']) ?> !important;
}

<?php endif; ?>
<?php if (!empty($site_theme['custom_color_3'])) : ?>
.cmn-btn.is-reset, .cmn-btn.is-gray, input[type="button"].cmn-btn.is-gray {
background-color: <?= h($site_theme['custom_color_3']) ?> !important;
}
<?php endif; ?>
