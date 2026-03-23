@extends('layouts.customer-builder')

@section('title', 'Edit Automation')
@section('page-title', 'Automation Builder')

@section('content')
<div class="h-screen overflow-hidden">
    <form method="POST" action="{{ route('customer.automations.update', $automation) }}" class="h-full flex flex-col">
        @csrf
        @method('PUT')

        <div class="shrink-0 border-b border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/80 backdrop-blur">
            <div class="px-4 py-3">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-3 lg:gap-4 lg:flex-1">
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Name</label>
                            <input
                                type="text"
                                name="name"
                                value="{{ $automation->name }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                required
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
                            <select
                                name="status"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                                <option value="draft" @selected($automation->status === 'draft')>Draft</option>
                                <option value="active" @selected($automation->status === 'active')>Active</option>
                                <option value="inactive" @selected($automation->status === 'inactive')>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <x-button href="{{ route('customer.automations.index') }}" variant="secondary">Back</x-button>
                        <x-button type="submit" variant="primary">Save</x-button>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="graph_json" value='@json($automation->graph ?? [])'>

        <div class="flex-1 min-h-0 p-4">
            <div
                id="automation-builder-react"
                class="h-full"
                data-full-height="true"
                data-automation-id="{{ $automation->id }}"
                data-initial-graph='@json($automation->graph ?? [])'
                data-email-lists='@json($emailLists->map(fn ($l) => ["id" => $l->id, "name" => $l->name])->values())'
                data-campaigns='@json($campaigns->map(fn ($c) => ["id" => $c->id, "name" => $c->name])->values())'
                data-templates='@json($templates->map(fn ($t) => ["id" => $t->id, "name" => $t->name])->values())'
                data-delivery-servers='@json($deliveryServers->map(fn ($s) => ["id" => $s->id, "name" => $s->name])->values())'
                data-hide-attribution="true"
            ></div>

            <div class="hidden" x-ignore>
                <div class="lg:col-span-3">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Nodes</h3>
                        </div>
                        <div class="mt-3 space-y-2">
                            <button type="button" class="w-full text-left rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5" @click="selectNode('trigger_1')">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Trigger</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Required</div>
                            </button>

                            <button type="button" class="w-full text-left rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5" @click="insertAfterSelected('email')">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Send Email</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Follow-up step</div>
                            </button>

                            <button type="button" class="w-full text-left rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5" @click="insertAfterSelected('delay')">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Delay</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Wait before next step</div>
                            </button>

                            <button type="button" class="w-full text-left rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5" @click="insertAfterSelected('condition')">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Condition</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Branching (basic)</div>
                            </button>

                            <button type="button" class="w-full text-left rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5" @click="insertAfterSelected('webhook')">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Webhook</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">HTTP request</div>
                            </button>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Use the + buttons in the flow to add steps.</div>
                            <x-button type="button" variant="table-danger" size="action" :pill="true" @click="deleteSelected()">Delete</x-button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
                        <div class="relative w-full h-[520px] overflow-auto" x-ref="viewport" @click="clearSelectionOnCanvas($event)">
                            <div class="relative automation-dotted-bg min-h-full" x-ref="canvas" x-bind:style="`width:${canvasWidth}px; height:${canvasHeight}px;`">
                                <svg class="absolute inset-0 w-full h-full pointer-events-none">
                                    <g x-ref="edgesLayer"></g>
                                </svg>

                                <template x-for="node in nodes" :key="node.id">
                                    <div
                                        data-node
                                        x-bind:data-node-id="node.id"
                                        class="absolute select-none rounded-lg border shadow-sm text-sm bg-white dark:bg-gray-800 dark:text-gray-100"
                                        x-bind:class="nodeClass(node)"
                                        x-bind:style="`left:${node.x}px; top:${node.y}px; width:${nodeWidth}px;`"
                                        @mousedown.prevent="startDrag($event, node)"
                                        @click.stop="onNodeClick(node)"
                                    >
                                        <div class="flex items-stretch">
                                            <div class="w-14 shrink-0 flex items-center justify-center border-r border-gray-200 dark:border-gray-700 bg-primary-50 dark:bg-primary-500/10">
                                                <div class="w-8 h-8 rounded-md bg-white dark:bg-gray-900 border border-primary-200 dark:border-primary-500/30 flex items-center justify-center text-primary-600 dark:text-primary-300">
                                                    <span class="text-xs font-semibold" x-text="node.type.slice(0, 1).toUpperCase()"></span>
                                                </div>
                                            </div>
                                            <div class="flex-1 px-4 py-3">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div>
                                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="node.label"></div>
                                                        <div class="mt-0.5 text-sm text-gray-600 dark:text-gray-300" x-text="node.subtitle || ''"></div>
                                                    </div>
                                                    <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click.stop="selectNode(node.id)">
                                                        <span class="text-lg leading-none">⋮</span>
                                                    </button>
                                                </div>

                                                <template x-if="node.type === 'condition'">
                                                    <div class="mt-3 flex items-center justify-between">
                                                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-emerald-300 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700" @click.stop="openInsertMenu(node, 'yes')">
                                                            <span>Yes</span>
                                                            <span class="text-base leading-none">+</span>
                                                        </button>
                                                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-rose-300 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700" @click.stop="openInsertMenu(node, 'no')">
                                                            <span>No</span>
                                                            <span class="text-base leading-none">+</span>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <button type="button" class="absolute left-1/2 -bottom-4 -translate-x-1/2 inline-flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-gray-900 border border-primary-300 dark:border-primary-500/40 text-primary-600 shadow-sm" @click.stop="openInsertMenu(node, null)">
                                            <span class="text-xl leading-none">+</span>
                                        </button>
                                    </div>
                                </template>

                                <template x-if="insertMenu.open">
                                    <div class="absolute z-30" x-bind:style="`left:${insertMenu.x}px; top:${insertMenu.y}px;`">
                                        <div class="w-48 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg p-2">
                                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 px-2 py-1">Add step</div>
                                            <button type="button" class="w-full text-left rounded-md px-2 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5" @click="insertNode('email')">Send Email</button>
                                            <button type="button" class="w-full text-left rounded-md px-2 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5" @click="insertNode('delay')">Delay</button>
                                            <button type="button" class="w-full text-left rounded-md px-2 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5" @click="insertNode('condition')">Condition</button>
                                            <button type="button" class="w-full text-left rounded-md px-2 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5" @click="insertNode('webhook')">Webhook</button>
                                            <div class="mt-1 px-2">
                                                <button type="button" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-200" @click="closeInsertMenu()">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Settings</h3>

                        <template x-if="!selectedNode">
                            <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Select a node to edit its settings.</div>
                        </template>

                        <template x-if="selectedNode">
                            <div class="mt-3 space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Node label</label>
                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.label" @input="syncSubtitle(selectedNode)">
                                </div>

                                <template x-if="selectedNode.type === 'trigger'">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Trigger event <span class="text-red-500">*</span></label>
                                            <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.trigger" @change="syncSubtitle(selectedNode)">
                                                <option value="">Select trigger</option>
                                                <option value="subscriber_added">Subscriber added</option>
                                                <option value="subscriber_confirmed">Subscriber confirmed</option>
                                                <option value="subscriber_unsubscribed">Subscriber unsubscribed</option>
                                                <option value="campaign_opened">Campaign opened</option>
                                                <option value="campaign_clicked">Campaign clicked</option>
                                                <option value="campaign_replied">Campaign replied</option>
                                                <option value="campaign_not_opened">Campaign not opened</option>
                                                <option value="campaign_not_replied">Campaign not replied</option>
                                                <option value="campaign_opened_not_clicked">Opened but not clicked</option>
                                            </select>
                                        </div>

                                        <template x-if="(selectedNode.settings.trigger || '').startsWith('subscriber_')">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Email list <span class="text-red-500">*</span></label>
                                                <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.list_id" @change="syncSubtitle(selectedNode)">
                                                    <option value="">Select list</option>
                                                    <template x-for="l in emailLists" :key="l.id">
                                                        <option :value="l.id" x-text="l.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </template>

                                        <template x-if="(selectedNode.settings.trigger || '').startsWith('campaign_')">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Campaign <span class="text-red-500">*</span></label>
                                                <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.campaign_id" @change="syncSubtitle(selectedNode)">
                                                    <option value="">Select campaign</option>
                                                    <template x-for="c in campaigns" :key="c.id">
                                                        <option :value="c.id" x-text="c.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </template>

                                        <template x-if="['campaign_not_opened','campaign_not_replied','campaign_opened_not_clicked'].includes(selectedNode.settings.trigger)">
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Check after <span class="text-red-500">*</span></label>
                                                    <input type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model.number="selectedNode.settings.window_value" @input="syncSubtitle(selectedNode)">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Unit <span class="text-red-500">*</span></label>
                                                    <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.window_unit" @change="syncSubtitle(selectedNode)">
                                                        <option value="minutes">minutes</option>
                                                        <option value="hours">hours</option>
                                                        <option value="days">days</option>
                                                        <option value="weeks">weeks</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="selectedNode.type === 'delay'">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Delay value <span class="text-red-500">*</span></label>
                                            <input type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model.number="selectedNode.settings.delay_value" @input="syncSubtitle(selectedNode)">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Delay unit <span class="text-red-500">*</span></label>
                                            <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.delay_unit" @change="syncSubtitle(selectedNode)">
                                                <option value="">Select unit</option>
                                                <option value="minutes">Minutes</option>
                                                <option value="hours">Hours</option>
                                                <option value="days">Days</option>
                                                <option value="weeks">Weeks</option>
                                            </select>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="selectedNode.type === 'email'">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Subject <span class="text-red-500">*</span></label>
                                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.subject" @input="syncSubtitle(selectedNode)">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Template <span class="text-red-500">*</span></label>
                                            <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.template_id" @change="syncSubtitle(selectedNode)">
                                                <option value="">Select template</option>
                                                <template x-for="t in templates" :key="t.id">
                                                    <option :value="t.id" x-text="t.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Delivery server</label>
                                            <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.delivery_server_id" @change="syncSubtitle(selectedNode)">
                                                <option value="">Default</option>
                                                <template x-for="s in deliveryServers" :key="s.id">
                                                    <option :value="s.id" x-text="s.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">From name</label>
                                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.from_name">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">From email</label>
                                            <input type="email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.from_email">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Reply-to</label>
                                            <input type="email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.reply_to">
                                        </div>

                                        <div class="space-y-2">
                                            <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                                                <input type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700" x-model="selectedNode.settings.track_opens">
                                                Track opens
                                            </label>
                                            <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                                                <input type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700" x-model="selectedNode.settings.track_clicks">
                                                Track clicks
                                            </label>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="selectedNode.type === 'webhook'">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">URL</label>
                                            <input type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.url" @input="syncSubtitle(selectedNode)">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Method</label>
                                            <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.method" @change="syncSubtitle(selectedNode)">
                                                <option value="POST">POST</option>
                                                <option value="GET">GET</option>
                                                <option value="PUT">PUT</option>
                                            </select>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="selectedNode.type === 'condition'">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Field</label>
                                            <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.field" @change="syncSubtitle(selectedNode)">
                                                <option value="">Select</option>
                                                <option value="email">Subscriber email</option>
                                                <option value="status">Subscriber status</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Operator</label>
                                            <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.operator" @change="syncSubtitle(selectedNode)">
                                                <option value="equals">Equals</option>
                                                <option value="contains">Contains</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Value</label>
                                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm" x-model="selectedNode.settings.value" @input="syncSubtitle(selectedNode)">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .automation-dotted-bg {
        background-color: #ffffff;
        background-image: radial-gradient(rgba(148, 163, 184, 0.55) 1px, transparent 1px);
        background-size: 16px 16px;
        background-position: 0 0;
    }

    .dark .automation-dotted-bg {
        background-color: rgb(17, 24, 39);
        background-image: radial-gradient(rgba(148, 163, 184, 0.18) 1px, transparent 1px);
    }
</style>
@endpush

@push('scripts')
@vite('resources/js/automation-builder.jsx')
<script>
(function () {
    document.addEventListener('alpine:init', () => {
        Alpine.data('automationBuilder', (config = {}) => ({
            name: config.initialName || '',
            status: config.initialStatus || 'draft',
            nodes: [],
            edges: [],
            selectedNodeId: null,
            dragging: null,
            flowMode: true,
            nodeWidth: 360,
            nodeHeight: 92,
            laneSpacing: 420,
            rowSpacing: 130,
            insertMenu: { open: false, x: 0, y: 0, fromId: null, branch: null },
            canvasWidth: 1200,
            canvasHeight: 900,
            measuredHeights: {},
            lastLayout: null,
            pendingScrollToId: null,
            emailLists: Array.isArray(config.emailLists) ? config.emailLists : [],
            campaigns: Array.isArray(config.campaigns) ? config.campaigns : [],
            templates: Array.isArray(config.templates) ? config.templates : [],
            deliveryServers: Array.isArray(config.deliveryServers) ? config.deliveryServers : [],

            init() {
                const g = (config.initialGraph && typeof config.initialGraph === 'object') ? config.initialGraph : {};
                this.nodes = Array.isArray(g.nodes) ? g.nodes.map((n) => ({
                    id: n.id,
                    type: n.type,
                    label: n.label,
                    subtitle: n.subtitle || '',
                    x: Number(n.x) || 40,
                    y: Number(n.y) || 40,
                    settings: (n.settings && typeof n.settings === 'object') ? n.settings : {},
                })) : [];

                this.edges = Array.isArray(g.edges) ? g.edges.map((e) => ({
                    id: e.id || this.uid('edge'),
                    from: e.from,
                    to: e.to,
                    branch: e.branch || undefined,
                })) : [];

                if (!this.nodes.length) {
                    this.nodes = [{ id: 'trigger_1', type: 'trigger', label: 'Trigger', subtitle: 'Start', x: 40, y: 40, settings: this.defaultSettingsFor('trigger') }];
                }

                let trigger = this.nodes.find((n) => n.id === 'trigger_1');
                if (!trigger) {
                    trigger = { id: 'trigger_1', type: 'trigger', label: 'Trigger', subtitle: 'Start', x: 40, y: 40, settings: this.defaultSettingsFor('trigger') };
                    this.nodes.unshift(trigger);
                }

                this.nodes.forEach((node) => {
                    const defaults = this.defaultSettingsFor(node.type);
                    const incoming = (node.settings && typeof node.settings === 'object') ? node.settings : {};
                    node.settings = Object.assign({}, defaults, incoming);
                    this.syncSubtitle(node);
                });

                this.selectedNodeId = trigger ? trigger.id : (this.nodes[0]?.id || null);

                this.$nextTick(() => {
                    this.autoLayout();
                    this.renderEdges();
                });

                window.addEventListener('mousemove', (e) => this.onDragMove(e));
                window.addEventListener('mouseup', () => this.endDrag());
            },

            get selectedNode() {
                if (!this.selectedNodeId) {
                    return null;
                }
                return this.nodes.find((n) => n.id === this.selectedNodeId) || null;
            },

            get graphJson() {
                return JSON.stringify({ nodes: this.nodes, edges: this.edges });
            },

            renderEdges() {
                const layer = this.$refs.edgesLayer;
                if (!layer) {
                    return;
                }

                while (layer.firstChild) {
                    layer.removeChild(layer.firstChild);
                }

                (this.edges || []).forEach((edge) => {
                    const p = this.edgePoints(edge);
                    const x1 = Number(p.x1) || 0;
                    const y1 = Number(p.y1) || 0;
                    const x2 = Number(p.x2) || 0;
                    const y2 = Number(p.y2) || 0;

                    const midY = y1 + Math.max(18, (y2 - y1) / 2);
                    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    path.setAttribute('d', `M ${x1} ${y1} L ${x1} ${midY} L ${x2} ${midY} L ${x2} ${y2}`);
                    path.setAttribute('fill', 'none');
                    path.setAttribute('stroke', 'rgba(59,130,246,0.9)');
                    path.setAttribute('stroke-width', '2');
                    path.setAttribute('stroke-linecap', 'round');
                    path.setAttribute('stroke-linejoin', 'round');
                    layer.appendChild(path);
                });
            },

            openInsertMenu(node, branch) {
                const viewport = this.$refs.viewport;
                if (!viewport || !node) {
                    return;
                }
                const maxX = viewport.scrollLeft + viewport.clientWidth - 200;
                const maxY = viewport.scrollTop + viewport.clientHeight - 220;
                const x = Math.max(8, Math.min(maxX, node.x + (this.nodeWidth / 2) - 96));
                const y = Math.max(8, Math.min(maxY, node.y + this.nodeActualHeight(node) + 26));
                this.insertMenu = { open: true, x, y, fromId: node.id, branch: branch || null };
            },

            refreshMeasurements() {
                const container = this.$refs.canvas;
                if (!container) {
                    return;
                }

                const heights = {};
                container.querySelectorAll('[data-node][data-node-id]').forEach((el) => {
                    const id = el.getAttribute('data-node-id');
                    if (!id) {
                        return;
                    }
                    const rect = el.getBoundingClientRect();
                    heights[id] = Math.ceil(rect.height || 0);
                });

                this.measuredHeights = heights;
            },

            nodeActualHeight(node) {
                if (!node) {
                    return this.nodeHeight;
                }
                const h = this.measuredHeights && this.measuredHeights[node.id];
                return (h && h > 0) ? h : this.nodeHeight;
            },

            applyMeasuredVerticalLayout() {
                if (!this.lastLayout) {
                    return;
                }

                const rowById = this.lastLayout.rowById || {};
                const laneById = this.lastLayout.laneById || {};
                const collapseLanes = !!this.lastLayout.collapseLanes;
                const laneOriginX = Number(this.lastLayout.laneOriginX) || 0;
                const laneStep = Number(this.lastLayout.laneStep) || this.laneSpacing;
                const centerX = Number(this.lastLayout.centerX) || 0;

                this.refreshMeasurements();

                let maxRow = 0;
                const rowHeights = {};
                this.nodes.forEach((n) => {
                    const row = rowById[n.id] ?? 0;
                    maxRow = Math.max(maxRow, row);
                    const h = this.nodeActualHeight(n);
                    const padded = h + 56;
                    rowHeights[row] = Math.max(rowHeights[row] || 0, padded);
                });

                const yForRow = {};
                let y = 24;
                const gap = 48;
                for (let r = 0; r <= maxRow; r++) {
                    yForRow[r] = y;
                    y += (rowHeights[r] || (this.nodeHeight + 56)) + gap;
                }

                this.nodes.forEach((n) => {
                    const l = collapseLanes ? 0 : (laneById[n.id] ?? 0);
                    const row = rowById[n.id] ?? 0;

                    if (collapseLanes) {
                        n.x = Math.max(8, centerX);
                    } else {
                        n.x = Math.max(8, laneOriginX + (l * laneStep));
                    }

                    n.y = Math.max(16, yForRow[row] ?? 24);
                });

                this.updateCanvasSize();

                if (this.pendingScrollToId) {
                    this.scrollToNode(this.pendingScrollToId);
                    this.pendingScrollToId = null;
                }
            },

            updateCanvasSize() {
                const padding = 80;
                let maxX = 0;
                let maxY = 0;
                this.nodes.forEach((n) => {
                    maxX = Math.max(maxX, (Number(n.x) || 0) + this.nodeWidth);
                    maxY = Math.max(maxY, (Number(n.y) || 0) + this.nodeActualHeight(n));
                });
                const viewport = this.$refs.viewport;
                const minW = viewport ? viewport.clientWidth : 0;
                const minH = viewport ? viewport.clientHeight : 0;
                this.canvasWidth = Math.max(minW, maxX + padding);
                this.canvasHeight = Math.max(minH, maxY + padding);
            },

            scrollToNode(nodeId) {
                const viewport = this.$refs.viewport;
                const node = this.nodes.find((n) => n.id === nodeId);
                if (!viewport || !node) {
                    return;
                }

                const y = Number(node.y) || 0;
                const x = Number(node.x) || 0;
                const h = this.nodeActualHeight(node);

                const targetTop = Math.max(0, y - (viewport.clientHeight / 2) + (h / 2));
                const targetLeft = Math.max(0, x - (viewport.clientWidth / 2) + (this.nodeWidth / 2));
                viewport.scrollTo({ top: targetTop, left: targetLeft, behavior: 'smooth' });
            },

            closeInsertMenu() {
                this.insertMenu = { open: false, x: 0, y: 0, fromId: null, branch: null };
            },

            insertNode(type) {
                const fromId = this.insertMenu.fromId;
                const branch = this.insertMenu.branch;
                this.closeInsertMenu();

                if (!fromId) {
                    return;
                }

                const fromNode = this.nodes.find((n) => n.id === fromId);
                const wantBranch = (fromNode && fromNode.type === 'condition') ? (branch || null) : null;
                const existingOut = (this.edges || []).find((e) => {
                    if (e.from !== fromId) {
                        return false;
                    }
                    if (wantBranch === null) {
                        return (e.branch === undefined || e.branch === null || e.branch === '');
                    }
                    return (e.branch || '') === wantBranch;
                });

                const base = {
                    trigger: { label: 'Trigger', subtitle: 'Start' },
                    email: { label: 'Send Email', subtitle: 'Email action' },
                    delay: { label: 'Delay', subtitle: 'Wait time' },
                    webhook: { label: 'Webhook', subtitle: 'HTTP request' },
                    condition: { label: 'Condition', subtitle: 'Branch' },
                };

                const meta = base[type] || { label: 'Action', subtitle: '' };

                const newNode = {
                    id: this.uid(type),
                    type,
                    label: meta.label,
                    subtitle: meta.subtitle,
                    x: 40,
                    y: 40,
                    settings: this.defaultSettingsFor(type),
                };

                this.nodes.push(newNode);

                if (existingOut && existingOut.to) {
                    const oldTo = existingOut.to;
                    existingOut.to = newNode.id;
                    if (wantBranch !== null) {
                        existingOut.branch = wantBranch;
                    } else {
                        existingOut.branch = undefined;
                    }
                    this.edges.push({ id: this.uid('edge'), from: newNode.id, to: oldTo });
                } else {
                    this.edges.push({ id: this.uid('edge'), from: fromId, to: newNode.id, branch: wantBranch || undefined });
                }
                this.selectedNodeId = newNode.id;
                this.pendingScrollToId = newNode.id;
                this.$nextTick(() => {
                    this.autoLayout();
                    this.renderEdges();
                });
            },

            insertAfterSelected(type) {
                const fromId = this.selectedNodeId || 'trigger_1';
                const from = this.nodes.find((n) => n.id === fromId);
                if (!from) {
                    return;
                }

                this.insertMenu = { open: false, x: 0, y: 0, fromId, branch: null };
                this.insertNode(type);
            },

            autoLayout() {
                const container = this.$refs.canvas;
                if (!container) {
                    return;
                }

                const bounds = container.getBoundingClientRect();
                const viewport = this.$refs.viewport;
                const viewLeft = viewport ? viewport.scrollLeft : 0;
                const viewWidth = viewport ? viewport.clientWidth : Math.max(0, bounds.width);
                const availableWidth = Math.max(0, viewWidth);
                const effectiveNodeWidth = Math.max(240, Math.min(this.nodeWidth, availableWidth - 16));
                this.nodeWidth = effectiveNodeWidth;
                const centerX = viewLeft + Math.max(8, (availableWidth - effectiveNodeWidth) / 2);

                const byId = {};
                this.nodes.forEach((n) => {
                    byId[n.id] = n;
                });

                const outgoing = {};
                (this.edges || []).forEach((e) => {
                    if (!outgoing[e.from]) {
                        outgoing[e.from] = [];
                    }
                    outgoing[e.from].push(e);
                });

                // For condition nodes:
                // - `branch: 'yes'` shifts left, `branch: 'no'` shifts right
                // - `branch: undefined` is the main (center) continuation and should NOT shift lanes.
                // Back-compat: if an older graph has exactly 2 outgoing edges with no branch info,
                // auto-assign yes/no so we still render the split deterministically.
                Object.keys(outgoing).forEach((k) => {
                    const node = byId[k];
                    if (!node || node.type !== 'condition') {
                        return;
                    }

                    const outs = outgoing[k] || [];
                    if (outs.length !== 2) {
                        return;
                    }

                    const b0 = (outs[0].branch || '').toLowerCase();
                    const b1 = (outs[1].branch || '').toLowerCase();
                    const hasAnyBranch = !!(b0 || b1);

                    if (!hasAnyBranch) {
                        outs[0].branch = 'yes';
                        outs[1].branch = 'no';
                        return;
                    }

                    if (b0 && !b1 && (b0 === 'yes' || b0 === 'no')) {
                        outs[1].branch = b0 === 'yes' ? 'no' : 'yes';
                        return;
                    }

                    if (b1 && !b0 && (b1 === 'yes' || b1 === 'no')) {
                        outs[0].branch = b1 === 'yes' ? 'no' : 'yes';
                        return;
                    }
                });

                const depth = {};
                const lane = {};
                const startId = byId['trigger_1'] ? 'trigger_1' : (this.nodes[0]?.id || null);
                if (!startId) {
                    return;
                }

                depth[startId] = 0;
                lane[startId] = 0;
                const q = [startId];
                while (q.length) {
                    const currentId = q.shift();
                    const current = byId[currentId];
                    const outs = outgoing[currentId] || [];

                    outs.forEach((edge) => {
                        const nextId = edge.to;
                        if (!byId[nextId]) {
                            return;
                        }

                        const nextDepth = (depth[currentId] ?? 0) + 1;
                        let changed = false;
                        if (depth[nextId] === undefined || nextDepth > depth[nextId]) {
                            depth[nextId] = nextDepth;
                            changed = true;
                        }

                        let nextLane = lane[currentId] ?? 0;
                        if (current && current.type === 'condition') {
                            if ((edge.branch || '') === 'yes') {
                                nextLane = nextLane - 1;
                            }
                            if ((edge.branch || '') === 'no') {
                                nextLane = nextLane + 1;
                            }
                        }

                        if (lane[nextId] === undefined) {
                            lane[nextId] = nextLane;
                        }

                        if (changed) {
                            q.push(nextId);
                        }
                    });
                }

                let minLane = 0;
                let maxLane = 0;
                this.nodes.forEach((n) => {
                    const l = lane[n.id] ?? 0;
                    minLane = Math.min(minLane, l);
                    maxLane = Math.max(maxLane, l);
                });

                let laneStep = this.laneSpacing;
                let laneOriginX = centerX;
                let collapseLanes = false;

                if (maxLane !== minLane) {
                    const leftBound = centerX + (minLane * laneStep);
                    const rightBound = centerX + (maxLane * laneStep) + effectiveNodeWidth;

                    const minX = viewLeft + 8;
                    const maxX = viewLeft + availableWidth - 8;

                    if (leftBound >= minX && rightBound <= maxX) {
                        // Fits with configured lane spacing; keep lane 0 anchored at centerX.
                    } else {
                        const maxLeftShift = centerX - minX;
                        const maxRightShift = maxX - (centerX + effectiveNodeWidth);
                        const maxSpan = maxLeftShift + maxRightShift;
                        const requiredSpan = maxLane - minLane;
                        const fitStep = requiredSpan > 0 ? (maxSpan / requiredSpan) : laneStep;

                        if (fitStep >= (effectiveNodeWidth + 40)) {
                            laneStep = fitStep;
                        } else {
                            collapseLanes = true;
                        }
                    }
                }

                const laneRows = {};
                const rowStep = Math.max(this.rowSpacing, this.nodeHeight + 80);
                const rowById = {};
                this.nodes
                    .slice()
                    .sort((a, b) => {
                        const da = depth[a.id] ?? 0;
                        const db = depth[b.id] ?? 0;
                        if (da !== db) {
                            return da - db;
                        }
                        return (a.id || '').localeCompare(b.id || '');
                    })
                    .forEach((n) => {
                        const l = collapseLanes ? 0 : (lane[n.id] ?? 0);
                        const desired = depth[n.id] ?? 0;
                        const last = laneRows[l] ?? -1;
                        const row = Math.max(desired, last + 1);
                        laneRows[l] = row;
                        rowById[n.id] = row;

                        if (collapseLanes) {
                            n.x = Math.max(8, centerX);
                        } else {
                            n.x = Math.max(8, laneOriginX + (l * laneStep));
                        }

                        n.y = Math.max(16, 24 + (row * rowStep));
                    });

                this.lastLayout = {
                    rowById,
                    laneById: lane,
                    collapseLanes,
                    laneOriginX,
                    laneStep,
                    centerX,
                };

                this.$nextTick(() => {
                    this.applyMeasuredVerticalLayout();
                    this.renderEdges();
                });
            },

            uid(prefix) {
                return `${prefix}_${Math.random().toString(16).slice(2)}_${Date.now()}`;
            },

            addNode(type) {
                const base = {
                    trigger: { label: 'Trigger', subtitle: 'Start' },
                    email: { label: 'Send Email', subtitle: 'Email action' },
                    delay: { label: 'Delay', subtitle: 'Wait time' },
                    webhook: { label: 'Webhook', subtitle: 'HTTP request' },
                    condition: { label: 'Condition', subtitle: 'Branch' },
                };

                const meta = base[type] || { label: 'Action', subtitle: '' };

                const newNode = {
                    id: this.uid(type),
                    type,
                    label: meta.label,
                    subtitle: meta.subtitle,
                    x: 80 + (this.nodes.length * 20),
                    y: 120 + (this.nodes.length * 18),
                    settings: this.defaultSettingsFor(type),
                };

                this.nodes.push(newNode);
                this.selectedNodeId = newNode.id;
            },

            defaultSettingsFor(type) {
                if (type === 'trigger') {
                    return { list_id: null, campaign_id: null, trigger: '', window_value: 24, window_unit: 'hours' };
                }
                if (type === 'delay') {
                    return { delay_value: 0, delay_unit: 'hours' };
                }
                if (type === 'email') {
                    return { subject: '', template_id: null, delivery_server_id: null, from_name: '', from_email: '', reply_to: '', track_opens: true, track_clicks: true };
                }
                if (type === 'webhook') {
                    return { url: '', method: 'POST' };
                }
                if (type === 'condition') {
                    return { field: '', operator: 'equals', value: '' };
                }
                return {};
            },

            selectNode(id) {
                const node = this.nodes.find((n) => n.id === id);
                if (node) {
                    this.selectedNodeId = node.id;
                }
            },

            onNodeClick(node) {
                this.selectedNodeId = node.id;
            },

            deleteSelected() {
                if (!this.selectedNodeId) {
                    return;
                }

                const id = this.selectedNodeId;

                if (id === 'trigger_1') {
                    return;
                }

                this.nodes = this.nodes.filter((n) => n.id !== id);
                this.edges = this.edges.filter((e) => e.from !== id && e.to !== id);
                this.selectedNodeId = null;
                this.$nextTick(() => {
                    this.autoLayout();
                    this.renderEdges();
                });
            },

            clearSelectionOnCanvas(e) {
                if (e.target && e.target.closest && e.target.closest('[data-node]')) {
                    return;
                }
                if (this.insertMenu.open) {
                    this.closeInsertMenu();
                    return;
                }
                this.selectedNodeId = null;
            },

            startDrag(e, node) {
                if (this.flowMode) {
                    return;
                }
                const rect = e.target.closest('div').getBoundingClientRect();
                this.dragging = {
                    id: node.id,
                    offsetX: e.clientX - rect.left,
                    offsetY: e.clientY - rect.top,
                };
            },

            onDragMove(e) {
                if (!this.dragging) {
                    return;
                }

                const container = this.$refs.canvas;
                if (!container) {
                    return;
                }

                const bounds = container.getBoundingClientRect();
                const x = e.clientX - bounds.left - this.dragging.offsetX;
                const y = e.clientY - bounds.top - this.dragging.offsetY;

                const node = this.nodes.find((n) => n.id === this.dragging.id);
                if (!node) {
                    return;
                }

                node.x = Math.max(0, Math.min(bounds.width - 180, x));
                node.y = Math.max(0, Math.min(bounds.height - 60, y));

                this.renderEdges();
            },

            endDrag() {
                this.dragging = null;
            },

            nodeClass(node) {
                const isSelected = this.selectedNodeId === node.id;
                return isSelected
                    ? 'border-primary-500 ring-2 ring-primary-500/30'
                    : 'border-gray-200 dark:border-gray-700';
            },

            edgePoints(edge) {
                const from = this.nodes.find((n) => n.id === edge.from);
                const to = this.nodes.find((n) => n.id === edge.to);

                if (!from || !to) {
                    return { x1: 0, y1: 0, x2: 0, y2: 0 };
                }

                return {
                    x1: from.x + (this.nodeWidth / 2),
                    y1: from.y + this.nodeActualHeight(from),
                    x2: to.x + (this.nodeWidth / 2),
                    y2: to.y,
                };
            },

            syncSubtitle(node) {
                if (!node) {
                    return;
                }

                if (node.type === 'trigger') {
                    const trigger = node.settings?.trigger || '';
                    const label = trigger ? trigger.replace(/_/g, ' ') : 'trigger';

                    if (trigger.startsWith('campaign_')) {
                        const c = this.campaigns.find((x) => Number(x.id) === Number(node.settings?.campaign_id));

                        const negativeTriggers = ['campaign_not_opened', 'campaign_not_replied', 'campaign_opened_not_clicked'];
                        if (negativeTriggers.includes(trigger)) {
                            const v = Number(node.settings?.window_value) || 0;
                            const u = node.settings?.window_unit || '';
                            node.subtitle = `${c ? c.name : 'No campaign'} • ${label} • after ${v} ${u}`;
                            return;
                        }

                        node.subtitle = `${c ? c.name : 'No campaign'} • ${label}`;
                        return;
                    }

                    const list = this.emailLists.find((l) => Number(l.id) === Number(node.settings?.list_id));
                    node.subtitle = `${list ? list.name : 'No list'} • ${label}`;
                    return;
                }

                if (node.type === 'delay') {
                    const v = node.settings?.delay_value;
                    const u = node.settings?.delay_unit;
                    if (v === undefined || v === null || u === undefined || u === null) {
                        node.subtitle = '';
                        return;
                    }
                    node.subtitle = `${v} ${u}`;
                    return;
                }

                if (node.type === 'email') {
                    const subject = (node.settings?.subject || '').trim();
                    node.subtitle = subject ? subject : 'Email';
                    return;
                }

                if (node.type === 'webhook') {
                    const url = (node.settings?.url || '').trim();
                    node.subtitle = url ? url : 'Webhook';
                    return;
                }

                if (node.type === 'condition') {
                    const f = node.settings?.field || '';
                    const op = node.settings?.operator || '';
                    const val = (node.settings?.value || '').trim();
                    node.subtitle = (f && op && val) ? `${f} ${op} ${val}` : 'Condition';
                    return;
                }
            },
        }));
    });
})();
</script>
@endpush
