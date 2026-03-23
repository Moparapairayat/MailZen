<?php

namespace App\Mail;

use App\Mail\Transport\SesReplyToTransport;
use Aws\Ses\SesClient;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Arr;

class CustomMailManager extends MailManager
{
    /**
     * Create an instance of the Symfony Amazon SES ReplyTo Transport driver.
     *
     * @param  array  $config
     * @return \App\Mail\Transport\SesReplyToTransport
     */
    protected function createSesReplyToTransport(array $config)
    {
        $config = array_merge(
            $this->app['config']->get('services.ses', []),
            $config
        );

        $config = Arr::except($config, ['transport']);

        return new SesReplyToTransport(
            new SesClient($this->addSesCredentials($config)),
            $config['options'] ?? []
        );
    }

    /**
     * Add AWS SES credentials to the configuration array.
     *
     * @param  array  $config
     * @return array
     */
    protected function addSesCredentials(array $config): array
    {
        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        unset($config['key'], $config['secret'], $config['token']);

        return $config;
    }
}
