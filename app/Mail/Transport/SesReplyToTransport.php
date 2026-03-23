<?php

namespace App\Mail\Transport;

use Aws\Exception\AwsException;
use Aws\Ses\SesClient;
use Illuminate\Support\Collection;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;

class SesReplyToTransport extends AbstractTransport
{
    /**
     * The Amazon SES instance.
     *
     * @var \Aws\Ses\SesClient
     */
    protected $ses;

    /**
     * The Amazon SES transmission options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Create a new SES ReplyTo transport instance.
     *
     * @param  \Aws\Ses\SesClient  $ses
     * @param  array  $options
     */
    public function __construct(SesClient $ses, $options = [])
    {
        $this->ses = $ses;
        $this->options = $options;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $originalMessage = $message->getOriginalMessage();
        
        if (!$originalMessage instanceof Message) {
            throw new TransportException('SesReplyToTransport only supports Symfony\Component\Mime\Message instances.');
        }

        $options = $this->options;

        // Add list management options if present
        if ($listManagementOptions = $this->listManagementOptions($message)) {
            $options['ListManagementOptions'] = $listManagementOptions;
        }

        // Add tags from metadata headers
        foreach ($originalMessage->getHeaders()->all() as $header) {
            if (strpos($header->getName(), 'X-') === 0) {
                $options['Tags'][] = ['Name' => substr($header->getName(), 2), 'Value' => $header->getBodyAsString()];
            }
        }

        // Extract email details
        $from = $originalMessage->getFrom();
        $toAddresses = [];
        $ccAddresses = [];
        $bccAddresses = [];
        $replyToAddresses = [];

        // Get recipients
        foreach ($originalMessage->getTo() as $address) {
            $toAddresses[] = $address->getAddress();
        }

        foreach ($originalMessage->getCc() as $address) {
            $ccAddresses[] = $address->getAddress();
        }

        foreach ($originalMessage->getBcc() as $address) {
            $bccAddresses[] = $address->getAddress();
        }

        // Get reply-to addresses
        foreach ($originalMessage->getReplyTo() as $address) {
            $replyToAddresses[] = $address->getAddress();
        }

        // Build destination array
        $destination = [];
        if (!empty($toAddresses)) {
            $destination['ToAddresses'] = $toAddresses;
        }
        if (!empty($ccAddresses)) {
            $destination['CcAddresses'] = $ccAddresses;
        }
        if (!empty($bccAddresses)) {
            $destination['BccAddresses'] = $bccAddresses;
        }

        // Build message parameters
        $params = array_merge($options, [
            'Source' => $from[0]->toString(),
            'Destination' => $destination,
            'Message' => [
                'Subject' => [
                    'Data' => $originalMessage->getSubject(),
                    'Charset' => 'UTF-8',
                ],
                'Body' => [
                    'Text' => [
                        'Data' => $originalMessage->getTextBody() ?? strip_tags($originalMessage->getHtmlBody() ?? ''),
                        'Charset' => 'UTF-8',
                    ],
                ],
            ],
        ]);

        if ($originalMessage instanceof Email) {
            $returnPath = $originalMessage->getReturnPath();
            if ($returnPath) {
                $returnPathAddress = trim((string) $returnPath->getAddress());
                if ($returnPathAddress !== '') {
                    $params['ReturnPath'] = $returnPathAddress;
                }
            }
        }

        // Add HTML body if present
        if ($originalMessage->getHtmlBody()) {
            $params['Message']['Body']['Html'] = [
                'Data' => $originalMessage->getHtmlBody(),
                'Charset' => 'UTF-8',
            ];
        }

        // Add ReplyToAddresses if present
        if (!empty($replyToAddresses)) {
            $params['ReplyToAddresses'] = $replyToAddresses;
        }

        try {
            $result = $this->ses->sendEmail($params);
        } catch (AwsException $e) {
            $reason = $e->getAwsErrorMessage() ?? $e->getMessage();

            throw new TransportException(
                sprintf('Request to AWS SES API failed. Reason: %s.', $reason),
                is_int($e->getCode()) ? $e->getCode() : 0,
                $e
            );
        }

        $messageId = $result->get('MessageId');

        $originalMessage->getHeaders()->addHeader('X-Message-ID', $messageId);
        $originalMessage->getHeaders()->addHeader('X-SES-Message-ID', $messageId);
    }

    /**
     * Extract the SES list management options, if applicable.
     *
     * @param  \Symfony\Component\Mailer\SentMessage  $message
     * @return array|null
     */
    protected function listManagementOptions(SentMessage $message)
    {
        $originalMessage = $message->getOriginalMessage();
        
        if ($header = $originalMessage->getHeaders()->get('X-SES-LIST-MANAGEMENT-OPTIONS')) {
            if (preg_match("/^(contactListName=)*(?<ContactListName>[^;]+)(;\s?topicName=(?<TopicName>.+))?$/ix", $header->getBodyAsString(), $listManagementOptions)) {
                return array_filter($listManagementOptions, fn ($e) => in_array($e, ['ContactListName', 'TopicName']), ARRAY_FILTER_USE_KEY);
            }
        }
    }

    /**
     * Get the Amazon SES client for the SesReplyToTransport instance.
     *
     * @return \Aws\Ses\SesClient
     */
    public function ses()
    {
        return $this->ses;
    }

    /**
     * Get the transmission options being used by the transport.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the transmission options being used by the transport.
     *
     * @param  array  $options
     * @return array
     */
    public function setOptions(array $options)
    {
        return $this->options = $options;
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'ses-reply-to';
    }
}
