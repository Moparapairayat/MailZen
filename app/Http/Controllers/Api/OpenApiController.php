<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OpenApiController extends Controller
{
    public function __invoke(Request $request)
    {
        $configuredAppUrl = trim((string) config('app.url', ''));
        $appUrl = $configuredAppUrl !== ''
            ? rtrim($configuredAppUrl, '/')
            : rtrim((string) $request->getSchemeAndHttpHost(), '/');

        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => (string) config('app.name', 'MailZen') . ' API',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => $appUrl . '/api'],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'API Token',
                    ],
                ],
                'schemas' => [
                    'Error' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            'security' => [
                ['bearerAuth' => []],
            ],
            'paths' => [
                '/billing/current' => [
                    'get' => [
                        'tags' => ['Billing'],
                        'summary' => 'Get current subscription and usage',
                        'responses' => [
                            '200' => [
                                'description' => 'OK',
                            ],
                            '401' => [
                                'description' => 'Unauthorized',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => '#/components/schemas/Error'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/billing/checkout/{plan}' => [
                    'post' => [
                        'tags' => ['Billing'],
                        'summary' => 'Create a checkout session for a plan',
                        'parameters' => [
                            [
                                'name' => 'plan',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'requestBody' => [
                            'required' => false,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success_url' => ['type' => 'string', 'nullable' => true],
                                            'cancel_url' => ['type' => 'string', 'nullable' => true],
                                            'coupon_code' => ['type' => 'string', 'nullable' => true],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/billing/cancel/{subscription}' => [
                    'post' => [
                        'tags' => ['Billing'],
                        'summary' => 'Cancel a subscription (cancel at period end)',
                        'parameters' => [
                            [
                                'name' => 'subscription',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '403' => ['description' => 'Forbidden'],
                        ],
                    ],
                ],
                '/billing/resume/{subscription}' => [
                    'post' => [
                        'tags' => ['Billing'],
                        'summary' => 'Resume a subscription',
                        'parameters' => [
                            [
                                'name' => 'subscription',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '403' => ['description' => 'Forbidden'],
                        ],
                    ],
                ],
                '/billing/history' => [
                    'get' => [
                        'tags' => ['Billing'],
                        'summary' => 'List subscription history',
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                ],
                '/billing/portal' => [
                    'get' => [
                        'tags' => ['Billing'],
                        'summary' => 'Create a customer billing portal URL',
                        'parameters' => [
                            [
                                'name' => 'return_url',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                ],

                '/v1/campaigns' => [
                    'get' => [
                        'tags' => ['Campaigns'],
                        'summary' => 'List campaigns',
                        'parameters' => [
                            ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'type', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Campaigns'],
                        'summary' => 'Create a campaign',
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/campaigns/{campaign}' => [
                    'get' => [
                        'tags' => ['Campaigns'],
                        'summary' => 'Get a campaign',
                        'parameters' => [
                            ['name' => 'campaign', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Campaigns'],
                        'summary' => 'Update a campaign',
                        'parameters' => [
                            ['name' => 'campaign', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Campaigns'],
                        'summary' => 'Delete a campaign',
                        'parameters' => [
                            ['name' => 'campaign', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/campaigns/{campaign}/start' => [
                    'post' => [
                        'tags' => ['Campaigns'],
                        'summary' => 'Start a campaign',
                        'parameters' => [
                            ['name' => 'campaign', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/campaigns/{campaign}/pause' => [
                    'post' => [
                        'tags' => ['Campaigns'],
                        'summary' => 'Pause a campaign',
                        'parameters' => [
                            ['name' => 'campaign', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/campaigns/{campaign}/resume' => [
                    'post' => [
                        'tags' => ['Campaigns'],
                        'summary' => 'Resume a campaign',
                        'parameters' => [
                            ['name' => 'campaign', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/campaigns/{campaign}/rerun' => [
                    'post' => [
                        'tags' => ['Campaigns'],
                        'summary' => 'Re-run a campaign',
                        'parameters' => [
                            ['name' => 'campaign', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/campaigns/{campaign}/stats' => [
                    'get' => [
                        'tags' => ['Campaigns'],
                        'summary' => 'Get campaign stats',
                        'parameters' => [
                            ['name' => 'campaign', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/campaigns/{campaign}/recipients' => [
                    'get' => [
                        'tags' => ['Campaigns'],
                        'summary' => 'List campaign recipients',
                        'parameters' => [
                            ['name' => 'campaign', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/v1/auto-responders' => [
                    'get' => [
                        'tags' => ['Auto Responders'],
                        'summary' => 'List auto responders',
                        'parameters' => [
                            ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'trigger', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Auto Responders'],
                        'summary' => 'Create an auto responder',
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/auto-responders/{autoResponder}' => [
                    'get' => [
                        'tags' => ['Auto Responders'],
                        'summary' => 'Get an auto responder',
                        'parameters' => [
                            ['name' => 'autoResponder', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Auto Responders'],
                        'summary' => 'Update an auto responder',
                        'parameters' => [
                            ['name' => 'autoResponder', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Auto Responders'],
                        'summary' => 'Delete an auto responder',
                        'parameters' => [
                            ['name' => 'autoResponder', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/auto-responders/{autoResponder}/start' => [
                    'post' => [
                        'tags' => ['Auto Responders'],
                        'summary' => 'Start (activate) an auto responder',
                        'parameters' => [
                            ['name' => 'autoResponder', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/auto-responders/{autoResponder}/pause' => [
                    'post' => [
                        'tags' => ['Auto Responders'],
                        'summary' => 'Pause (deactivate) an auto responder',
                        'parameters' => [
                            ['name' => 'autoResponder', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/auto-responders/{autoResponder}/resume' => [
                    'post' => [
                        'tags' => ['Auto Responders'],
                        'summary' => 'Resume (activate) an auto responder',
                        'parameters' => [
                            ['name' => 'autoResponder', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/auto-responders/{autoResponder}/rerun' => [
                    'post' => [
                        'tags' => ['Auto Responders'],
                        'summary' => 'Re-run an auto responder for a specific subscriber',
                        'parameters' => [
                            ['name' => 'autoResponder', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'subscriber_id' => ['type' => 'integer'],
                                        ],
                                        'required' => ['subscriber_id'],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/auto-responders/{autoResponder}/stats' => [
                    'get' => [
                        'tags' => ['Auto Responders'],
                        'summary' => 'Get auto responder stats',
                        'parameters' => [
                            ['name' => 'autoResponder', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/auto-responders/{autoResponder}/recipients' => [
                    'get' => [
                        'tags' => ['Auto Responders'],
                        'summary' => 'List auto responder deliveries (recipients)',
                        'parameters' => [
                            ['name' => 'autoResponder', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/v1/delivery-servers' => [
                    'get' => [
                        'tags' => ['Delivery Servers'],
                        'summary' => 'List delivery servers',
                        'parameters' => [
                            ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Delivery Servers'],
                        'summary' => 'Create delivery server',
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/delivery-servers/{deliveryServer}' => [
                    'get' => [
                        'tags' => ['Delivery Servers'],
                        'summary' => 'Get delivery server',
                        'parameters' => [
                            ['name' => 'deliveryServer', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Delivery Servers'],
                        'summary' => 'Update delivery server',
                        'parameters' => [
                            ['name' => 'deliveryServer', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Delivery Servers'],
                        'summary' => 'Delete delivery server',
                        'parameters' => [
                            ['name' => 'deliveryServer', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/delivery-servers/{deliveryServer}/test-email' => [
                    'post' => [
                        'tags' => ['Delivery Servers'],
                        'summary' => 'Send a test email using a delivery server',
                        'parameters' => [
                            ['name' => 'deliveryServer', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/delivery-servers/{deliveryServer}/verify' => [
                    'post' => [
                        'tags' => ['Delivery Servers'],
                        'summary' => 'Verify a delivery server',
                        'parameters' => [
                            ['name' => 'deliveryServer', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/delivery-servers/{deliveryServer}/resend-verification' => [
                    'post' => [
                        'tags' => ['Delivery Servers'],
                        'summary' => 'Resend verification for a delivery server',
                        'parameters' => [
                            ['name' => 'deliveryServer', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/v1/bounce-servers' => [
                    'get' => [
                        'tags' => ['Bounce Servers'],
                        'summary' => 'List bounce servers',
                        'parameters' => [
                            ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Bounce Servers'],
                        'summary' => 'Create bounce server',
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/bounce-servers/{bounceServer}' => [
                    'get' => [
                        'tags' => ['Bounce Servers'],
                        'summary' => 'Get bounce server',
                        'parameters' => [
                            ['name' => 'bounceServer', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Bounce Servers'],
                        'summary' => 'Update bounce server',
                        'parameters' => [
                            ['name' => 'bounceServer', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Bounce Servers'],
                        'summary' => 'Delete bounce server',
                        'parameters' => [
                            ['name' => 'bounceServer', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/v1/sending-domains' => [
                    'get' => [
                        'tags' => ['Sending Domains'],
                        'summary' => 'List sending domains',
                        'parameters' => [
                            ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Sending Domains'],
                        'summary' => 'Create sending domain',
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/sending-domains/{sendingDomain}' => [
                    'get' => [
                        'tags' => ['Sending Domains'],
                        'summary' => 'Get sending domain',
                        'parameters' => [
                            ['name' => 'sendingDomain', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Sending Domains'],
                        'summary' => 'Update sending domain',
                        'parameters' => [
                            ['name' => 'sendingDomain', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Sending Domains'],
                        'summary' => 'Delete sending domain',
                        'parameters' => [
                            ['name' => 'sendingDomain', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/sending-domains/{sendingDomain}/verify' => [
                    'post' => [
                        'tags' => ['Sending Domains'],
                        'summary' => 'Verify sending domain by checking DNS',
                        'parameters' => [
                            ['name' => 'sendingDomain', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/sending-domains/{sendingDomain}/mark-verified' => [
                    'post' => [
                        'tags' => ['Sending Domains'],
                        'summary' => 'Manually mark sending domain as verified',
                        'parameters' => [
                            ['name' => 'sendingDomain', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/v1/tracking-domains' => [
                    'get' => [
                        'tags' => ['Tracking Domains'],
                        'summary' => 'List tracking domains',
                        'parameters' => [
                            ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Tracking Domains'],
                        'summary' => 'Create tracking domain',
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/tracking-domains/{trackingDomain}' => [
                    'get' => [
                        'tags' => ['Tracking Domains'],
                        'summary' => 'Get tracking domain',
                        'parameters' => [
                            ['name' => 'trackingDomain', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Tracking Domains'],
                        'summary' => 'Update tracking domain',
                        'parameters' => [
                            ['name' => 'trackingDomain', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Tracking Domains'],
                        'summary' => 'Delete tracking domain',
                        'parameters' => [
                            ['name' => 'trackingDomain', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/tracking-domains/{trackingDomain}/verify' => [
                    'post' => [
                        'tags' => ['Tracking Domains'],
                        'summary' => 'Verify tracking domain',
                        'parameters' => [
                            ['name' => 'trackingDomain', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/tracking-domains/{trackingDomain}/mark-verified' => [
                    'post' => [
                        'tags' => ['Tracking Domains'],
                        'summary' => 'Manually mark tracking domain as verified',
                        'parameters' => [
                            ['name' => 'trackingDomain', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/v1/lists' => [
                    'get' => [
                        'tags' => ['Lists'],
                        'summary' => 'List email lists',
                        'parameters' => [
                            ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Lists'],
                        'summary' => 'Create email list',
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/lists/{list}' => [
                    'get' => [
                        'tags' => ['Lists'],
                        'summary' => 'Get a single email list',
                        'parameters' => [
                            ['name' => 'list', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Lists'],
                        'summary' => 'Update email list',
                        'parameters' => [
                            ['name' => 'list', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Lists'],
                        'summary' => 'Delete email list',
                        'parameters' => [
                            ['name' => 'list', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/v1/lists/{list}/contacts' => [
                    'get' => [
                        'tags' => ['Contacts'],
                        'summary' => 'List contacts in an email list',
                        'parameters' => [
                            ['name' => 'list', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Contacts'],
                        'summary' => 'Create a contact in an email list',
                        'parameters' => [
                            ['name' => 'list', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/lists/{list}/contacts/{subscriber}' => [
                    'get' => [
                        'tags' => ['Contacts'],
                        'summary' => 'Get a single contact from an email list',
                        'parameters' => [
                            ['name' => 'list', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'subscriber', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Contacts'],
                        'summary' => 'Update a contact in an email list',
                        'parameters' => [
                            ['name' => 'list', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'subscriber', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Contacts'],
                        'summary' => 'Delete a contact from an email list',
                        'parameters' => [
                            ['name' => 'list', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'subscriber', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/v1/lists/{list}/contacts/import/json' => [
                    'post' => [
                        'tags' => ['Imports'],
                        'summary' => 'Import contacts via JSON payload (synchronous)',
                        'parameters' => [
                            ['name' => 'list', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/lists/{list}/contacts/import/csv' => [
                    'post' => [
                        'tags' => ['Imports'],
                        'summary' => 'Import contacts via CSV upload (queued, returns 202)',
                        'parameters' => [
                            ['name' => 'list', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '202' => ['description' => 'Accepted'],
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],

                '/v1/transactional-emails' => [
                    'get' => [
                        'tags' => ['Transactional Emails'],
                        'summary' => 'List transactional email templates',
                        'parameters' => [
                            ['name' => 'q', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Transactional Emails'],
                        'summary' => 'Create a transactional email template',
                        'responses' => [
                            '201' => ['description' => 'Created'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/v1/transactional-emails/{transactionalEmail}' => [
                    'get' => [
                        'tags' => ['Transactional Emails'],
                        'summary' => 'Get a transactional email template',
                        'parameters' => [
                            ['name' => 'transactionalEmail', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Transactional Emails'],
                        'summary' => 'Update a transactional email template',
                        'parameters' => [
                            ['name' => 'transactionalEmail', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Transactional Emails'],
                        'summary' => 'Delete a transactional email template',
                        'parameters' => [
                            ['name' => 'transactionalEmail', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/v1/transactional-emails/send' => [
                    'post' => [
                        'tags' => ['Transactional Emails'],
                        'summary' => 'Send a transactional email using a template',
                        'description' => 'Send an email using a pre-defined template. Variables in the template ({{variable}}) will be replaced with provided values.',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['to'],
                                        'properties' => [
                                            'template_key' => ['type' => 'string', 'description' => 'Template key (required if template_id not provided)'],
                                            'template_id' => ['type' => 'integer', 'description' => 'Template ID (required if template_key not provided)'],
                                            'to' => ['type' => 'string', 'format' => 'email', 'description' => 'Recipient email address'],
                                            'to_name' => ['type' => 'string', 'description' => 'Recipient name'],
                                            'variables' => ['type' => 'object', 'description' => 'Key-value pairs to replace {{variable}} in template'],
                                            'from_email' => ['type' => 'string', 'format' => 'email', 'description' => 'Override from email'],
                                            'from_name' => ['type' => 'string', 'description' => 'Override from name'],
                                            'reply_to' => ['type' => 'string', 'format' => 'email'],
                                            'subject' => ['type' => 'string', 'description' => 'Override subject'],
                                            'delivery_server_id' => ['type' => 'integer', 'description' => 'Specific delivery server to use'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Email sent successfully'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Template not found'],
                            '422' => ['description' => 'Validation error or no delivery server'],
                            '500' => ['description' => 'Failed to send email'],
                        ],
                    ],
                ],
                '/v1/transactional-emails/send-raw' => [
                    'post' => [
                        'tags' => ['Transactional Emails'],
                        'summary' => 'Send a raw transactional email without a template',
                        'description' => 'Send an email directly with HTML/plain text content without using a template.',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['to', 'subject'],
                                        'properties' => [
                                            'to' => ['type' => 'string', 'format' => 'email', 'description' => 'Recipient email address'],
                                            'to_name' => ['type' => 'string', 'description' => 'Recipient name'],
                                            'subject' => ['type' => 'string', 'description' => 'Email subject'],
                                            'html_content' => ['type' => 'string', 'description' => 'HTML email content'],
                                            'plain_text_content' => ['type' => 'string', 'description' => 'Plain text email content'],
                                            'from_email' => ['type' => 'string', 'format' => 'email'],
                                            'from_name' => ['type' => 'string'],
                                            'reply_to' => ['type' => 'string', 'format' => 'email'],
                                            'delivery_server_id' => ['type' => 'integer', 'description' => 'Specific delivery server to use'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Email sent successfully'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error or no delivery server'],
                            '500' => ['description' => 'Failed to send email'],
                        ],
                    ],
                ],
                '/v1/transactional-emails/send-bulk' => [
                    'post' => [
                        'tags' => ['Transactional Emails'],
                        'summary' => 'Send bulk transactional emails to multiple recipients',
                        'description' => 'Send the same template to up to 1000 recipients. Each recipient can have unique variables.',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['recipients'],
                                        'properties' => [
                                            'template_key' => ['type' => 'string', 'description' => 'Template key (required if template_id not provided)'],
                                            'template_id' => ['type' => 'integer', 'description' => 'Template ID (required if template_key not provided)'],
                                            'recipients' => [
                                                'type' => 'array',
                                                'maxItems' => 1000,
                                                'items' => [
                                                    'type' => 'object',
                                                    'required' => ['to'],
                                                    'properties' => [
                                                        'to' => ['type' => 'string', 'format' => 'email'],
                                                        'to_name' => ['type' => 'string'],
                                                        'variables' => ['type' => 'object', 'description' => 'Per-recipient variables'],
                                                    ],
                                                ],
                                            ],
                                            'from_email' => ['type' => 'string', 'format' => 'email'],
                                            'from_name' => ['type' => 'string'],
                                            'reply_to' => ['type' => 'string', 'format' => 'email'],
                                            'subject' => ['type' => 'string', 'description' => 'Override subject'],
                                            'delivery_server_id' => ['type' => 'integer'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Bulk send completed with results'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Template not found'],
                            '422' => ['description' => 'Validation error or no delivery server'],
                        ],
                    ],
                ],

                '/admin/v1/customers' => [
                    'get' => [
                        'tags' => ['Admin'],
                        'summary' => 'List customers (supports ?q= for search)',
                        'parameters' => [
                            ['name' => 'q', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                ],

                '/admin/v1/plans' => [
                    'get' => [
                        'tags' => ['Admin - Plans'],
                        'summary' => 'List plans',
                        'parameters' => [
                            ['name' => 'q', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Admin - Plans'],
                        'summary' => 'Create a plan',
                        'responses' => [
                            '201' => ['description' => 'Created'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/admin/v1/plans/{plan}' => [
                    'get' => [
                        'tags' => ['Admin - Plans'],
                        'summary' => 'Get a plan',
                        'parameters' => [
                            ['name' => 'plan', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Admin - Plans'],
                        'summary' => 'Update a plan',
                        'parameters' => [
                            ['name' => 'plan', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Admin - Plans'],
                        'summary' => 'Delete a plan',
                        'parameters' => [
                            ['name' => 'plan', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/admin/v1/coupons' => [
                    'get' => [
                        'tags' => ['Admin - Coupons'],
                        'summary' => 'List coupons',
                        'parameters' => [
                            ['name' => 'q', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Admin - Coupons'],
                        'summary' => 'Create a coupon',
                        'responses' => [
                            '201' => ['description' => 'Created'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/admin/v1/coupons/{coupon}' => [
                    'get' => [
                        'tags' => ['Admin - Coupons'],
                        'summary' => 'Get a coupon',
                        'parameters' => [
                            ['name' => 'coupon', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Admin - Coupons'],
                        'summary' => 'Update a coupon',
                        'parameters' => [
                            ['name' => 'coupon', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Admin - Coupons'],
                        'summary' => 'Delete a coupon',
                        'parameters' => [
                            ['name' => 'coupon', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/admin/v1/blog-posts' => [
                    'get' => [
                        'tags' => ['Admin - Blog'],
                        'summary' => 'List blog posts',
                        'parameters' => [
                            ['name' => 'q', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Admin - Blog'],
                        'summary' => 'Create a blog post',
                        'responses' => [
                            '201' => ['description' => 'Created'],
                            '401' => ['description' => 'Unauthorized'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/admin/v1/blog-posts/{blogPost}' => [
                    'get' => [
                        'tags' => ['Admin - Blog'],
                        'summary' => 'Get a blog post',
                        'parameters' => [
                            ['name' => 'blogPost', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Admin - Blog'],
                        'summary' => 'Update a blog post',
                        'parameters' => [
                            ['name' => 'blogPost', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Admin - Blog'],
                        'summary' => 'Delete a blog post',
                        'parameters' => [
                            ['name' => 'blogPost', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/admin/v1/support-tickets' => [
                    'get' => [
                        'tags' => ['Admin - Support'],
                        'summary' => 'List support tickets',
                        'parameters' => [
                            ['name' => 'q', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'priority', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                ],
                '/admin/v1/support-tickets/{supportTicket}' => [
                    'get' => [
                        'tags' => ['Admin - Support'],
                        'summary' => 'Get a support ticket with messages',
                        'parameters' => [
                            ['name' => 'supportTicket', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Admin - Support'],
                        'summary' => 'Update ticket status/priority',
                        'parameters' => [
                            ['name' => 'supportTicket', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Admin - Support'],
                        'summary' => 'Delete a support ticket',
                        'parameters' => [
                            ['name' => 'supportTicket', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/admin/v1/support-tickets/{supportTicket}/reply' => [
                    'post' => [
                        'tags' => ['Admin - Support'],
                        'summary' => 'Reply to a support ticket',
                        'parameters' => [
                            ['name' => 'supportTicket', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '201' => ['description' => 'Created'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/admin/v1/affiliates' => [
                    'get' => [
                        'tags' => ['Admin - Affiliates'],
                        'summary' => 'List affiliates',
                        'parameters' => [
                            ['name' => 'q', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                ],
                '/admin/v1/affiliates/{affiliate}' => [
                    'get' => [
                        'tags' => ['Admin - Affiliates'],
                        'summary' => 'Get an affiliate with referrals and commissions',
                        'parameters' => [
                            ['name' => 'affiliate', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'put' => [
                        'tags' => ['Admin - Affiliates'],
                        'summary' => 'Update affiliate (status, commission rate)',
                        'parameters' => [
                            ['name' => 'affiliate', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Admin - Affiliates'],
                        'summary' => 'Delete an affiliate',
                        'parameters' => [
                            ['name' => 'affiliate', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],

                '/admin/v1/invoices' => [
                    'get' => [
                        'tags' => ['Admin - Invoices'],
                        'summary' => 'List invoices/subscriptions',
                        'parameters' => [
                            ['name' => 'q', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                        ],
                    ],
                ],
                '/admin/v1/invoices/{invoice}' => [
                    'get' => [
                        'tags' => ['Admin - Invoices'],
                        'summary' => 'Get an invoice/subscription',
                        'parameters' => [
                            ['name' => 'invoice', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '401' => ['description' => 'Unauthorized'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
            ],
        ];

        return response()->json($spec);
    }
}
