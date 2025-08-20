<aside class="input-flow-wrap l-main">
    <?php if ((string)$stepNum === '3') : ?>
        <ol class="input-flow -list d-flex">
            <li class="flowInput <?php if ($currentStep === 1): ?>is-current<?php endif; ?>">
                <div class="flow-block">
                    <span class="flowNum">1</span>
                    <p><?= $this->Tr->h($step1) ?></p>
                </div>
            </li>
            <li class="flowConfirm <?php if ($currentStep === 2): ?>is-current<?php endif; ?>">
                <div class="flow-block">
                    <span class="flowNum">2</span>
                    <p><?= $this->Tr->h($step2) ?></p>
                </div>
            </li>
            <li class="flowComplete <?php if ($currentStep === 3): ?>is-current<?php endif; ?>">
                <div class="flow-block">
                    <span class="flowNum">3</span>
                    <p><?= $this->Tr->h($step3) ?></p>
                </div>
            </li>
        </ol>
    <?php else: ?>
        <ol class="input-flow -list d-flex">
            <li class="flowInput <?php if ($currentStep === 1): ?>is-current<?php endif; ?>">
                <div class="flow-block">
                    <span class="flowNum">1</span>
                    <p><?= $this->Tr->h($step1) ?></p>
                </div>
            </li>
            <li class="flowComplete <?php if ($currentStep === 2): ?>is-current<?php endif; ?>">
                <div class="flow-block">
                    <span class="flowNum">2</span>
                    <p><?= $this->Tr->h($step2) ?></p>
                </div>
            </li>
        </ol>
    <?php endif; ?>
</aside>
