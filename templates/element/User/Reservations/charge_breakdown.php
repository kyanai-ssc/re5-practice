<tr class="field-input">
    <th class="ttl-input">
        <div class="ttl-input-wrap">
            <?= $this->Tr->h('reservation/chargeBreakDown') ?>
        </div>
    </th>
    <td>
        <div class="schedule-header sticky pc-only">
            <div class="is-listOnly pc-only">
                <div class="history_list_head">
                    <ul>
                        <li class="h-name">
                            <?= $this->Tr->h('reservation/chargeBreakDown/name') ?>
                        </li>
                        <li class="h-dayTime">
                            <?= $this->Tr->h('reservation/chargeBreakDown/charge') ?>
                        </li>
                        <li class="h-num">
                            <?= $this->Tr->h('reservation/chargeBreakDown/number') ?>
                        </li>
                        <li class="h-confirm">
                            <?= $this->Tr->h('reservation/chargeBreakDown/totalCharge') ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="history_list_body is-continue">
            <ul>
                <?php foreach ($reservationForms as $reservationForm): ?>
                    <?php foreach ($reservationForm->getChargeBreakdown() as $breakDown): ?>
                        <li class="list_body_line_wrap clearfix">
                            <ul class="list_body_line">
                                <li class="b-name for_txt">
                                    <?= h($breakDown['name']) ?>
                                </li>
                                <li class="b-dayTime">
                                    <?php foreach ($breakDown['charges'] as $charge): ?>
                                        <?php if (isset($charge['name'])): ?>
                                            <?= h($charge['name']) ?>　
                                        <?php endif; ?>
                                        <?= h($charge['charge']) ?><?= $this->Tr->h('reservation/chargeUnit') ?><br/>
                                    <?php endforeach; ?>
                                </li>
                                <li class="b-num">
                                    <?= h($breakDown['number']) ?><?= h($breakDown['unit']) ?>
                                </li>
                                <li class="b-confirm">
                                    <?= h($breakDown['totalCharge']) ?><?= $this->Tr->h('reservation/chargeUnit') ?>
                                </li>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </td>
</tr>
