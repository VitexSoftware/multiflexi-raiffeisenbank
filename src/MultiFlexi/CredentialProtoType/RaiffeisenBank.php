<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi\CredentialProtoType;

/**
 * Description of RaiffeisenBank.
 *
 * author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class RaiffeisenBank extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface, \MultiFlexi\checkableCredentialInterface
{
    /**
     * Widgets for displaying certificate metadata in the config form.
     */
    public array $certificateWidgets = [];
    public static string $logo = 'RaiffeisenBank.svg';

    public function __construct()
    {
        parent::__construct();

        // Define internal configuration fields
        $accountNumberField = new \MultiFlexi\ConfigField('ACCOUNT_NUMBER', 'string', _('Raiffeisen Bank Account Number'), _('Number of the Raiffeisen Bank account'));
        $accountNumberField->setHint('123456789/5500')->setValue('');

        $currencyField = new \MultiFlexi\ConfigField('ACCOUNT_CURRENCY', 'string', _('Account Currency'), _('Currency of the account (e.g., CZK, EUR, USD)'));
        $currencyField->setHint('CZK')->setValue('CZK');

        $certFileField = new \MultiFlexi\ConfigField('CERT_FILE', 'string', _('Certificate File Path'), _('Path to the certificate file'));
        $certFileField->setHint('/path/to/certificate.p12')->setValue('');

        $certPassField = new \MultiFlexi\ConfigField('CERT_PASS', 'password', _('Certificate Password'), _('Password for the certificate file'));
        $certPassField->setHint('your-password')->setValue('');

        $clientIdField = new \MultiFlexi\ConfigField('XIBMCLIENTID', 'string', _('Client ID'), _('X-IBM-Client-Id for API access'));
        $clientIdField->setHint('your-client-id')->setValue('');

        $rateFile = new \MultiFlexi\ConfigField('RBAPI_RATE_LIMIT_JSON_FILE', 'string', 'RBAPI_RATE_LIMIT_JSON_FILE', '', '/tmp/rbczpremiumapi_rates.json', \MultiFlexi\Defaults::$MULTIFLEXI_TMP.'/rbczpremiumapi_rates.json');
        $rateFile->setManual(false);
        $this->configFieldsProvided->addField($rateFile);

        $rateWaitMode = new \MultiFlexi\ConfigField('RBAPI_RATE_WAIT_MODE', 'bool', 'RBAPI_RATE_WAIT_MODE', _('If true, wait (bounded by RBAPI_RATE_MAX_WAIT_SECONDS) for the rate-limit window to reset instead of failing immediately'), 'false', 'false');
        $rateWaitMode->setManual(false);
        $this->configFieldsProvided->addField($rateWaitMode);

        $rateMaxWait = new \MultiFlexi\ConfigField('RBAPI_RATE_MAX_WAIT_SECONDS', 'integer', 'RBAPI_RATE_MAX_WAIT_SECONDS', _('Longest wait (in seconds) allowed in RBAPI_RATE_WAIT_MODE before giving up instead of blocking the job'), '300', '300');
        $rateMaxWait->setManual(false);
        $this->configFieldsProvided->addField($rateMaxWait);

        $this->configFieldsInternal->addField($accountNumberField);
        $this->configFieldsInternal->addField($currencyField);
        $this->configFieldsInternal->addField($certFileField);
        $this->configFieldsInternal->addField($certPassField);
        $this->configFieldsInternal->addField($clientIdField);
    }

    public function load(int $credTypeId)
    {
        $loaded = parent::load($credTypeId);

        // Load provided configuration fields
        foreach ($this->configFieldsInternal->getFields() as $field) {
            $this->configFieldsProvided->addField($field);
        }

        return $loaded;
    }

    #[\Override]
    public function prepareConfigForm(): void
    {
        // Implement the configuration form logic if needed
    }

    public function name(): string
    {
        return _('Raiffeisen Bank');
    }

    public function description(): string
    {
        return _('Raiffeisen Bank Premium API');
    }

    public function uuid(): string
    {
        return '8f1193f6-82f6-48d5-a2f7-4c7defce5443';
    }

    #[\Override]
    public function logo(): string
    {
        return self::$logo;
    }

    #[\Override]
    public function checkAvailability(): \MultiFlexi\CredentialCheckResult
    {
        $f        = fn (string $c) => (string) ($this->configFieldsInternal->getFieldByCode($c)?->getValue() ?? '');
        $certFile = $f('CERT_FILE');
        $certPass = $f('CERT_PASS');
        $clientId = $f('XIBMCLIENTID');

        // 1) Offline validation
        if ($clientId === '' || $certFile === '' || !is_readable($certFile)) {
            return new \MultiFlexi\CredentialCheckResult(
                \MultiFlexi\CredentialState::Misconfigured,
                _('Client ID or readable certificate file is missing'),
                time(),
            );
        }

        $certContent = file_get_contents($certFile);

        if ($certContent === false || !openssl_pkcs12_read($certContent, $certs, $certPass)) {
            return new \MultiFlexi\CredentialCheckResult(
                \MultiFlexi\CredentialState::Misconfigured,
                _('Cannot open certificate with the given password'),
                time(),
            );
        }

        // 2) Respect the rate-limit budget — avoid spending a request when exhausted
        $rateFile = (string) ($this->configFieldsProvided->getFieldByCode('RBAPI_RATE_LIMIT_JSON_FILE')?->getValue() ?? '');

        if ($rateFile !== '' && is_readable($rateFile)) {
            $rateContent = file_get_contents($rateFile);

            if ($rateContent !== false) {
                $rates = json_decode($rateContent, true);

                if (isset($rates['remaining']) && (int) $rates['remaining'] <= 0) {
                    return new \MultiFlexi\CredentialCheckResult(
                        \MultiFlexi\CredentialState::Available,
                        _('Rate budget exhausted — assuming available'),
                        time(),
                        60,
                    );
                }
            }
        }

        // 3) Live authenticated call via RB Premium connector (add dependency when available).
        // Until then, certificate validity is sufficient as a gate.
        return new \MultiFlexi\CredentialCheckResult(
            \MultiFlexi\CredentialState::Available,
            '',
            time(),
            300,
        );
    }
}
