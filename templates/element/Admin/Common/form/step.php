<?php if((string)$stepNum === '3') :?>
<ol class="cd-breadcrumb triangle clearfix crumb-2">
          <li class="<?php if((string)$currentStep === '1'):?>current<?php endif;?>"><span><?= h($step1) ?></span></li>
          <li class="<?php if((string)$currentStep === '2'):?>current<?php endif;?>"><span><?= h($step2) ?></span></li>
          <li class="<?php if((string)$currentStep === '3'):?>current<?php endif;?>"><span><?= h($step3) ?></span></li>
        </ol>

<?php else: ?>
<ol class="cd-breadcrumb triangle clearfix crumb-3">
  <li class="<?php if((string)$currentStep === '1'):?>current<?php endif;?>"><span><?= h($step1) ?></span></li>
  <li class="<?php if((string)$currentStep === '2'):?><?php endif;?>"><span><?= h($step2) ?></span></li>
</ol>
<?php endif;?>
