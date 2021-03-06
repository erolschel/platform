<?php declare(strict_types=1);
echo $app->getContainer()->get('renderer')->fetch('_header.php'); ?>

<div class="card__title">
    <h2><?= $t->t('configuration_header'); ?></h2>
</div>

<form action="<?= $menuHelper->getCurrentUrl(); ?>" method="post">
    <div class="card__body">
    <?php if ($error): ?>
        <div class="alert alert-error">
            <pre><?= $error; ?></pre>
        </div>
    <?php endif; ?>

    <p><?= $t->t('configuration_sconfig_text'); ?></p>

    <p>
        <label for="c_config_shopName"><?= $t->t('configuration_sconfig_name'); ?></label>
        <input type="text"
               value="<?= isset($parameters['c_config_shopName']) ? $parameters['c_config_shopName'] : ''; ?>"
               name="c_config_shopName"
               id="c_config_shopName"
               required="required"
               autofocus/>
    </p>

    <p>
        <label for="c_config_mail">
            <?= $t->t('configuration_sconfig_mail'); ?>
            <a class="help-badge"
               href="#"
               data-tooltip="<?= $t->t('configuration_email_help_text'); ?>">
                <i class="icon-help"></i>
            </a>
        </label>
        <input type="email"
               value="<?= isset($parameters['c_config_mail']) ? $parameters['c_config_mail'] : ''; ?>"
               name="c_config_mail"
               id="c_config_mail"
               required="required"/>
    </p>

    <div class="form-group form-group--50">
        <div class="input-group">
            <label for="c_config_shop_language"><?= $t->t('configuration_sconfig_language'); ?></label>
            <div class="select-wrapper">
                <select name="c_config_shop_language" id="c_config_shop_language">
                    <?php foreach ($languageIsos as $iso): ?>
                        <option value="<?= $iso; ?>" <?= $parameters['c_config_shop_language'] === $iso ? 'selected' : ''; ?>>
                            <?= $t->t('select_language_' . substr($iso, 0, 2)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="input-group">
            <label for="c_config_shop_currency"><?= $t->t('configuration_sconfig_currency'); ?></label>
            <div class="select-wrapper">
                <select name="c_config_shop_currency" id="c_config_shop_currency">
                    <option
                        value="EUR" <?= $parameters['c_config_shop_currency'] === 'EUR' ? 'selected' : ''; ?>><?= $t->t('configuration_admin_currency_eur'); ?></option>
                    <option
                        value="USD" <?= $parameters['c_config_shop_currency'] === 'USD' ? 'selected' : ''; ?>><?= $t->t('configuration_admin_currency_usd'); ?></option>
                    <option
                        value="GBP" <?= $parameters['c_config_shop_currency'] === 'GBP' ? 'selected' : ''; ?>><?= $t->t('configuration_admin_currency_gbp'); ?></option>
                </select>
            </div>
            <span class="help-block" style="display: none">
               <?= $t->t('configuration_sconfig_currency_info'); ?>
            </span>
        </div>
    </div>

        <div class="alert alert-warning">
            <span class="icon-warning"></span>
            <?= $t->t('configuration_defaults_warning'); ?>
        </div>

        <hr>

        <p>
            <label for="c_config_admin_email"><?= $t->t('configuration_admin_mail'); ?></label>
            <input type="email"
                   value="<?= isset($parameters['c_config_admin_email']) ? $parameters['c_config_admin_email'] : ''; ?>"
                   name="c_config_admin_email"
                   id="c_config_admin_email"
                   required="required"/>
        </p>

        <div class="form-group form-group--50">
            <p>
                <label for="c_config_admin_firstName"><?= $t->t('configuration_admin_firstName'); ?></label>
                <input type="text"
                       value="<?= isset($parameters['c_config_admin_firstName']) ? $parameters['c_config_admin_firstName'] : ''; ?>"
                       name="c_config_admin_firstName"
                       id="c_config_admin_firstName"
                       required="required"/>
            </p>

            <p>
                <label for="c_config_admin_lastName"><?= $t->t('configuration_admin_lastName'); ?></label>
                <input type="text"
                       value="<?= isset($parameters['c_config_admin_lastName']) ? $parameters['c_config_admin_lastName'] : ''; ?>"
                       name="c_config_admin_lastName"
                       id="c_config_admin_lastName"
                       required="required"/>
            </p>
        </div>

        <div class="form-group form-group--50">
            <p>
                <label for="c_config_admin_username"><?= $t->t('configuration_admin_username'); ?></label>
                <input type="text"
                       value="<?= isset($parameters['c_config_admin_username']) ? $parameters['c_config_admin_username'] : ''; ?>"
                       name="c_config_admin_username"
                       id="c_config_admin_username"
                       required="required"/>
            </p>

            <p>
                <label for="c_config_admin_password"><?= $t->t('configuration_admin_password'); ?></label>
                <input type="password"
                       value="<?= isset($parameters['c_config_admin_password']) ? $parameters['c_config_admin_password'] : ''; ?>"
                       name="c_config_admin_password"
                       id="c_config_admin_password"
                       required="required"/>
            </p>
        </div>
    </div>

    <div class="card__footer flex-container">
        <a href="<?= $menuHelper->getPreviousUrl(); ?>"
           class="btn btn-default flex-item"><?= $t->t('back'); ?></a>
        <button type="submit"
                class="btn btn-primary flex-item flex-right">
            <?= $t->t('forward'); ?>
        </button>
    </div>
</form>

<?php echo $app->getContainer()->get('renderer')->fetch('_footer.php'); ?>
