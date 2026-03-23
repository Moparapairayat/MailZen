import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import ReactFlow, {
    addEdge,
    Background,
    Controls,
    Handle,
    MarkerType,
    MiniMap,
    Position,
    ReactFlowProvider,
    useEdgesState,
    useNodesState,
    useReactFlow,
} from 'reactflow';
import 'reactflow/dist/style.css';

const TRIGGER_LABELS = {
    subscriber_added: 'Subscriber added',
    subscriber_confirmed: 'Subscribed',
    subscriber_unsubscribed: 'Unsubscribed',
    webhook_received: 'Webhook received',
    wp_user_registered: 'WP user registered',
    wp_user_updated: 'WP user updated',
    woo_customer_created: 'Woo customer created',
    woo_order_created: 'Woo order created',
    woo_order_paid: 'Woo order paid',
    woo_order_completed: 'Woo order completed',
    woo_order_refunded: 'Woo order refunded',
    woo_order_cancelled: 'Woo order cancelled',
    woo_abandoned_checkout: 'Woo abandoned checkout',
    campaign_opened: 'Campaign opened',
    campaign_clicked: 'Campaign clicked',
    campaign_replied: 'Campaign replied',
    campaign_not_opened: 'Campaign not opened',
    campaign_not_replied: 'Campaign not replied',
    campaign_opened_not_clicked: 'Opened but not clicked',
};

const TRIGGER_CATEGORIES = [
    {
        type: 'List',
        options: [
            { value: 'subscriber_added', label: TRIGGER_LABELS.subscriber_added },
            { value: 'subscriber_confirmed', label: TRIGGER_LABELS.subscriber_confirmed },
            { value: 'subscriber_unsubscribed', label: TRIGGER_LABELS.subscriber_unsubscribed },
        ],
    },
    {
        type: 'WordPress',
        options: [
            { value: 'wp_user_registered', label: TRIGGER_LABELS.wp_user_registered },
            { value: 'wp_user_updated', label: TRIGGER_LABELS.wp_user_updated },
        ],
    },
    {
        type: 'WooCommerce',
        options: [
            { value: 'woo_customer_created', label: TRIGGER_LABELS.woo_customer_created },
            { value: 'woo_order_created', label: TRIGGER_LABELS.woo_order_created },
            { value: 'woo_order_paid', label: TRIGGER_LABELS.woo_order_paid },
            { value: 'woo_order_completed', label: TRIGGER_LABELS.woo_order_completed },
            { value: 'woo_order_refunded', label: TRIGGER_LABELS.woo_order_refunded },
            { value: 'woo_order_cancelled', label: TRIGGER_LABELS.woo_order_cancelled },
            { value: 'woo_abandoned_checkout', label: TRIGGER_LABELS.woo_abandoned_checkout },
        ],
    },
    {
        type: 'Webhook',
        options: [
            { value: 'webhook_received', label: TRIGGER_LABELS.webhook_received },
        ],
    },
    {
        type: 'Campaigns',
        options: [
            { value: 'campaign_opened', label: TRIGGER_LABELS.campaign_opened },
            { value: 'campaign_clicked', label: TRIGGER_LABELS.campaign_clicked },
            { value: 'campaign_replied', label: TRIGGER_LABELS.campaign_replied },
            { value: 'campaign_not_opened', label: TRIGGER_LABELS.campaign_not_opened },
            { value: 'campaign_not_replied', label: TRIGGER_LABELS.campaign_not_replied },
            { value: 'campaign_opened_not_clicked', label: TRIGGER_LABELS.campaign_opened_not_clicked },
        ],
    },
    
];

function getTriggerLabel(trigger) {
    if (!trigger) return '';
    return TRIGGER_LABELS[trigger] || trigger.replace(/_/g, ' ');
}

function safeJsonParse(raw, fallback) {
    try {
        if (!raw) return fallback;
        const v = JSON.parse(raw);
        return v ?? fallback;
    } catch {
        return fallback;
    }
}

function syncSubtitle(node, { emailLists, campaigns } = {}) {
    if (!node) {
        return '';
    }

    if (node.type === 'trigger') {
        const trigger = node.settings?.trigger || '';
        const label = trigger ? getTriggerLabel(trigger) : 'trigger';

        if (trigger === 'webhook_received') {
            const l = (emailLists || []).find((x) => Number(x.id) === Number(node.settings?.list_id));
            return `${l ? l.name : 'Any source'} • ${label}`;
        }

        if (trigger.startsWith('campaign_')) {
            const c = (campaigns || []).find((x) => Number(x.id) === Number(node.settings?.campaign_id));
            const negativeTriggers = ['campaign_not_opened', 'campaign_not_replied', 'campaign_opened_not_clicked'];
            if (negativeTriggers.includes(trigger)) {
                const v = Number(node.settings?.window_value) || 0;
                const u = node.settings?.window_unit || '';
                return `${c ? c.name : 'No campaign'} • ${label} • after ${v} ${u}`;
            }
            return `${c ? c.name : 'No campaign'} • ${label}`;
        }

        const list = (emailLists || []).find((l) => Number(l.id) === Number(node.settings?.list_id));
        return `${list ? list.name : 'No list'} • ${label}`;
    }

    if (node.type === 'delay') {
        const v = node.settings?.delay_value;
        const u = node.settings?.delay_unit;
        if (v === undefined || v === null || u === undefined || u === null) {
            return '';
        }
        return `${v} ${u}`;
    }

    if (node.type === 'email') {
        const subject = (node.settings?.subject || '').trim();
        return subject ? subject : 'Email';
    }

    if (node.type === 'run_campaign') {
        const c = (campaigns || []).find((x) => Number(x.id) === Number(node.settings?.campaign_id));
        return c ? `Run ${c.name}` : 'Run campaign';
    }

    if (node.type === 'move_subscribers') {
        const list = (emailLists || []).find((l) => Number(l.id) === Number(node.settings?.target_list_id));
        return list ? `Move to ${list.name}` : 'Move to list';
    }

    if (node.type === 'copy_subscribers') {
        const list = (emailLists || []).find((l) => Number(l.id) === Number(node.settings?.target_list_id));
        return list ? `Copy to ${list.name}` : 'Copy to list';
    }

    if (node.type === 'webhook') {
        const url = (node.settings?.url || '').trim();
        return url ? url : 'Webhook';
    }

    if (node.type === 'condition') {
        const f = node.settings?.field || '';
        const op = node.settings?.operator || '';
        const val = (node.settings?.value || '').trim();
        return (f && op && val) ? `${f} ${op} ${val}` : 'Condition';
    }

    return node.subtitle || '';
}

function uid(prefix) {
    return `${prefix}_${Math.random().toString(16).slice(2)}_${Date.now()}`;
}

function collectDownstreamIds(startId, edgesList) {
    if (!startId) return new Set();
    const out = new Map();
    (edgesList || []).forEach((e) => {
        if (!e?.source || !e?.target) return;
        const s = String(e.source);
        const t = String(e.target);
        if (!out.has(s)) out.set(s, []);
        out.get(s).push(t);
    });

    const seen = new Set();
    const stack = [String(startId)];
    while (stack.length) {
        const cur = String(stack.pop());
        if (seen.has(cur)) continue;
        seen.add(cur);
        const next = out.get(cur) || [];
        next.forEach((n) => {
            if (!seen.has(String(n))) stack.push(String(n));
        });
    }
    return seen;
}

function NodeIcon({ type }) {
    const svgClass = 'w-5 h-5';
    const common = {
        className: svgClass,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg',
        stroke: 'currentColor',
        strokeWidth: 2,
        strokeLinecap: 'round',
        strokeLinejoin: 'round',
    };

    if (type === 'trigger') {
        return (
            <svg {...common}>
                <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z" />
            </svg>
        );
    }
    if (type === 'email') {
        return (
            <svg {...common}>
                <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
                <rect x="2" y="4" width="20" height="16" rx="2" />
            </svg>
        );
    }
    if (type === 'delay') {
        return (
            <svg {...common}>
                <circle cx="12" cy="12" r="8" />
                <path d="M12 8v5l3 2" />
            </svg>
        );
    }
    if (type === 'condition') {
        return (
            <svg {...common}>
                <line x1="6" x2="6" y1="3" y2="15" />
                <circle cx="18" cy="6" r="3" />
                <circle cx="6" cy="18" r="3" />
                <path d="M18 9a9 9 0 0 1-9 9" />
            </svg>
        );
    }
    if (type === 'webhook') {
        return (
            <svg {...common}>
                <path d="M18 16.98h-5.99c-1.1 0-1.95.94-2.48 1.9A4 4 0 0 1 2 17c.01-.7.2-1.4.57-2" />
                <path d="m6 17 3.13-5.78c.53-.97.1-2.18-.5-3.1a4 4 0 1 1 6.89-4.06" />
                <path d="m12 6 3.13 5.73C15.66 12.7 16.9 13 18 13a4 4 0 0 1 0 8" />
            </svg>
        );
    }
    if (type === 'run_campaign') {
        return (
            <svg {...common}>
                <path d="M11 6a13 13 0 0 0 8.4-2.8A1 1 0 0 1 21 4v12a1 1 0 0 1-1.6.8A13 13 0 0 0 11 14H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z" />
                <path d="M6 14a12 12 0 0 0 2.4 7.2 2 2 0 0 0 3.2-2.4A8 8 0 0 1 10 14" />
                <path d="M8 6v8" />
            </svg>
        );
    }
    if (type === 'move_subscribers') {
        return (
            <svg {...common}>
                <path d="m16 3 4 4-4 4" />
                <path d="M20 7H4" />
                <path d="m8 21-4-4 4-4" />
                <path d="M4 17h16" />
            </svg>
        );
    }
    if (type === 'copy_subscribers') {
        return (
            <svg {...common}>
                <rect width="14" height="14" x="8" y="8" rx="2" ry="2" />
                <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" />
            </svg>
        );
    }

    return <span className="text-xs font-semibold">{String(type || '?').slice(0, 1).toUpperCase()}</span>;
}

function BaseNode({ data, selected }) {
    const isSelected = !!selected;

    return (
        <div
            className={
                `rounded-lg border shadow-sm bg-white dark:bg-gray-800 text-sm overflow-hidden ` +
                (isSelected
                    ? 'border-primary-600 dark:border-primary-400 ring-2 ring-primary-500/25'
                    : 'border-gray-200 dark:border-gray-700')
            }
            style={{ width: 340 }}
        >
            <div className="flex items-stretch">
                <div className="w-14 shrink-0 flex items-center justify-center border-r border-gray-200 dark:border-gray-700 bg-primary-50 dark:bg-primary-500/10">
                    <div className="w-8 h-8 rounded-md bg-white dark:bg-gray-900 border border-primary-200 dark:border-primary-500/30 flex items-center justify-center text-primary-600 dark:text-primary-300">
                        <NodeIcon type={String(data?.type || '')} />
                    </div>
                </div>
                <div className="flex-1 px-4 py-3">
                    <div className="flex items-start justify-between gap-2">
                        <div>
                            <div className="text-sm font-semibold text-gray-900 dark:text-gray-100">{data?.label || 'Node'}</div>
                            <div className="mt-0.5 text-sm text-gray-600 dark:text-gray-300">{data?.subtitle || ''}</div>
                        </div>
                        <button
                            type="button"
                            className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            onClick={(e) => {
                                e.stopPropagation();
                                data?.onSelect?.(data?.id);
                            }}
                        >
                            <span className="text-lg leading-none">⋮</span>
                        </button>
                    </div>

                    {data?.type === 'condition' ? (
                        <div className="mt-3 flex items-center justify-between">
                            <button
                                type="button"
                                className="inline-flex items-center gap-2 rounded-full border border-emerald-300 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    data?.onInsert?.(data?.id, 'yes', { x: e.clientX, y: e.clientY });
                                }}
                            >
                                <span>Yes</span>
                                <span className="text-base leading-none">+</span>
                            </button>
                            <button
                                type="button"
                                className="inline-flex items-center gap-2 rounded-full border border-rose-300 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    data?.onInsert?.(data?.id, 'no', { x: e.clientX, y: e.clientY });
                                }}
                            >
                                <span>No</span>
                                <span className="text-base leading-none">+</span>
                            </button>
                        </div>
                    ) : null}
                </div>
            </div>

            <button
                type="button"
                className="absolute left-1/2 -bottom-4 -translate-x-1/2 inline-flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-gray-900 border border-primary-300 dark:border-primary-500/40 text-primary-600 shadow-sm"
                onClick={(e) => {
                    e.stopPropagation();
                    data?.onInsert?.(data?.id, null, { x: e.clientX, y: e.clientY });
                }}
            >
                <span className="text-xl leading-none">+</span>
            </button>

            <Handle type="target" position={Position.Top} style={{ opacity: 0 }} />
            <Handle type="source" position={Position.Bottom} style={{ opacity: 0 }} />
            {data?.type === 'condition' ? (
                <>
                    <Handle type="source" id="yes" position={Position.Bottom} style={{ opacity: 0, left: 80 }} />
                    <Handle type="source" id="no" position={Position.Bottom} style={{ opacity: 0, left: 260 }} />
                </>
            ) : null}
        </div>
    );
}

const nodeTypes = {
    base: BaseNode,
};

function toFlowNodes(graphNodes, callbacks) {
    const byId = new Map();
    (graphNodes || []).forEach((n) => {
        if (n && n.id) byId.set(n.id, n);
    });

    return (graphNodes || []).map((n) => {
        const pos = {
            x: Number(n?.x) || 0,
            y: Number(n?.y) || 0,
        };
        return {
            id: String(n.id),
            type: 'base',
            position: pos,
            data: {
                id: String(n.id),
                type: n.type,
                label: n.label,
                subtitle: n.subtitle,
                settings: n.settings || {},
                onSelect: callbacks.onSelect,
                onInsert: callbacks.onInsert,
            },
        };
    });
}

function toFlowEdges(graphEdges) {
    return (graphEdges || []).map((e) => {
        const id = String(e.id || uid('edge'));
        const branch = e.branch || undefined;
        return {
            id,
            source: String(e.from),
            target: String(e.to),
            sourceHandle: branch === 'yes' ? 'yes' : branch === 'no' ? 'no' : undefined,
            type: 'smoothstep',
            markerEnd: {
                type: MarkerType.ArrowClosed,
                width: 16,
                height: 16,
                color: 'rgba(59,130,246,0.9)',
            },
            style: {
                stroke: 'rgba(59,130,246,0.9)',
                strokeWidth: 2,
            },
            data: {
                branch,
            },
        };
    });
}

function fromFlow(nodes, edges, graphNodeMetaById) {
    const outNodes = nodes.map((n) => {
        const meta = graphNodeMetaById.get(n.id) || {};
        return {
            id: n.id,
            type: meta.type || n.data?.type || 'action',
            label: meta.label || n.data?.label || 'Node',
            subtitle: meta.subtitle || n.data?.subtitle || '',
            x: n.position?.x ?? 0,
            y: n.position?.y ?? 0,
            settings: meta.settings || n.data?.settings || {},
        };
    });

    const outEdges = edges.map((e) => {
        return {
            id: e.id,
            from: e.source,
            to: e.target,
            branch: e.data?.branch || undefined,
        };
    });

    return { nodes: outNodes, edges: outEdges };
}

function ensureTrigger(graph) {
    const nodes = Array.isArray(graph.nodes) ? [...graph.nodes] : [];
    const edges = Array.isArray(graph.edges) ? [...graph.edges] : [];

    let trigger = nodes.find((n) => n.id === 'trigger_1');
    if (!trigger) {
        trigger = {
            id: 'trigger_1',
            type: 'trigger',
            label: 'Trigger',
            subtitle: 'Start',
            x: 0,
            y: 0,
            settings: { list_id: null, campaign_id: null, trigger: '', window_value: 24, window_unit: 'hours', webhook_token: '' },
        };
        nodes.unshift(trigger);
    }

    return { nodes, edges };
}

function simpleVerticalLayout(graph) {
    const nodeWidth = 340;
    const rowGap = 170;

    const nodes = [...(graph.nodes || [])];
    const edges = [...(graph.edges || [])];

    const byId = new Map(nodes.map((n) => [n.id, n]));
    const outgoing = new Map();
    edges.forEach((e) => {
        if (!outgoing.has(e.from)) outgoing.set(e.from, []);
        outgoing.get(e.from).push(e);
    });

    // Depth via BFS from trigger
    const depth = new Map();
    const lane = new Map();
    const startId = byId.has('trigger_1') ? 'trigger_1' : (nodes[0]?.id || null);
    if (!startId) return { nodes, edges };

    depth.set(startId, 0);
    lane.set(startId, 0);

    const q = [startId];
    while (q.length) {
        const currentId = q.shift();
        const current = byId.get(currentId);
        const outs = outgoing.get(currentId) || [];

        outs.forEach((edge) => {
            if (!byId.has(edge.to)) return;

            const nextDepth = (depth.get(currentId) ?? 0) + 1;
            const prevDepth = depth.get(edge.to);
            if (prevDepth === undefined || nextDepth > prevDepth) {
                depth.set(edge.to, nextDepth);
                q.push(edge.to);
            }

            let nextLane = lane.get(currentId) ?? 0;
            if (current?.type === 'condition') {
                if ((edge.branch || '') === 'yes') nextLane -= 1;
                if ((edge.branch || '') === 'no') nextLane += 1;
            }

            if (!lane.has(edge.to)) {
                lane.set(edge.to, nextLane);
            }
        });
    }

    // Center lane 0
    const laneStep = 420;
    const centerX = 0;

    // Assign positions
    nodes.forEach((n) => {
        const d = depth.get(n.id) ?? 0;
        const l = lane.get(n.id) ?? 0;
        n.x = centerX + l * laneStep;
        n.y = d * rowGap;
    });

    // Move to positive space
    const padX = 60;
    const padY = 60;

    const anchor = byId.get('trigger_1') || nodes[0] || { x: 0, y: 0 };
    const anchorX = Number(anchor.x) || 0;
    const anchorY = Number(anchor.y) || 0;
    nodes.forEach((n) => {
        n.x = n.x - anchorX + padX;
        n.y = n.y - anchorY + padY;
    });

    return { nodes, edges, nodeWidth };
}

function SettingsPanel({ selected, setSelected, emailLists, campaigns, templates, deliveryServers, onUpdateSelected, automationId, onDeleteSelected }) {
    if (!selected) {
        return (
            <div className="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-gray-100">Settings</h3>
                <div className="mt-3 text-sm text-gray-500 dark:text-gray-400">Select a node to edit its settings.</div>
            </div>
        );
    }

    const setField = (path, value) => {
        onUpdateSelected((n) => {
            const next = { ...n };
            if (path === 'label') next.label = value;
            if (path === 'settings') next.settings = { ...(next.settings || {}), ...(value || {}) };
            return next;
        });
    };

    const trigger = (selected.settings?.trigger || '');

    const generateToken = () => {
        try {
            const bytes = new Uint8Array(24);
            window.crypto.getRandomValues(bytes);
            return Array.from(bytes)
                .map((b) => b.toString(16).padStart(2, '0'))
                .join('');
        } catch {
            return Math.random().toString(16).slice(2) + Math.random().toString(16).slice(2);
        }
    };

    const webhookUrl = useMemo(() => {
        if (!automationId) return '';
        const token = selected.settings?.webhook_token || '';
        const base = `${window.location.origin}/webhooks/automations/${automationId}`;
        return token ? `${base}?token=${encodeURIComponent(token)}` : base;
    }, [automationId, selected.settings?.webhook_token]);

    return (
        <div className="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div className="flex items-start justify-between gap-2">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-gray-100">Settings</h3>
                <div className="flex items-center gap-2">
                    {selected.id !== 'trigger_1' ? (
                        <button
                            type="button"
                            className="text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                            onClick={() => onDeleteSelected?.()}
                        >
                            Delete
                        </button>
                    ) : null}
                    <button type="button" className="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-200" onClick={() => setSelected(null)}>
                        Close
                    </button>
                </div>
            </div>

            <div className="mt-3 space-y-4">
                <div>
                    <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Node label</label>
                    <input
                        type="text"
                        className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                        value={selected.label || ''}
                        onChange={(e) => setField('label', e.target.value)}
                    />
                </div>

                {selected.type === 'trigger' ? (
                    <div className="space-y-3">
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Trigger event <span className="text-red-500">*</span></label>
                            <select
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={trigger}
                                onChange={(e) => {
                                    const next = e.target.value;
                                    const patch = { trigger: next };

                                    if (next === 'webhook_received' && !selected.settings?.webhook_token) {
                                        patch.webhook_token = generateToken();
                                    }

                                    setField('settings', patch);
                                }}
                            >
                                <option value="">Select trigger</option>
                                {TRIGGER_CATEGORIES.map((group) => (
                                    <optgroup
                                        key={group.type}
                                        label={group.disabled ? `${group.type} (Coming soon)` : group.type}
                                        disabled={!!group.disabled}
                                    >
                                        {group.options.map((opt) => (
                                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                                        ))}
                                    </optgroup>
                                ))}
                            </select>
                        </div>

                        {trigger.startsWith('subscriber_') ? (
                            <div>
                                <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Email list <span className="text-red-500">*</span></label>
                                <select
                                    className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                    value={selected.settings?.list_id || ''}
                                    onChange={(e) => setField('settings', { list_id: e.target.value || null })}
                                >
                                    <option value="">Select list</option>
                                    {(emailLists || []).map((l) => (
                                        <option key={l.id} value={l.id}>{l.name}</option>
                                    ))}
                                </select>
                            </div>
                        ) : null}

                        {(trigger.startsWith('wp_') || trigger.startsWith('woo_')) ? (
                            <div>
                                <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Email list (optional)</label>
                                <select
                                    className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                    value={selected.settings?.list_id || ''}
                                    onChange={(e) => setField('settings', { list_id: e.target.value || null })}
                                >
                                    <option value="">Any list</option>
                                    {(emailLists || []).map((l) => (
                                        <option key={l.id} value={l.id}>{l.name}</option>
                                    ))}
                                </select>
                            </div>
                        ) : null}

                        {trigger === 'webhook_received' ? (
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Email list (optional)</label>
                                    <select
                                        className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                        value={selected.settings?.list_id || ''}
                                        onChange={(e) => setField('settings', { list_id: e.target.value || null })}
                                    >
                                        <option value="">Auto (Webhook contacts)</option>
                                        {(emailLists || []).map((l) => (
                                            <option key={l.id} value={l.id}>{l.name}</option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Webhook token <span className="text-red-500">*</span></label>
                                    <div className="mt-1 flex items-center gap-2">
                                        <input
                                            type="text"
                                            className="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                            value={selected.settings?.webhook_token || ''}
                                            onChange={(e) => setField('settings', { webhook_token: e.target.value })}
                                        />
                                        <button
                                            type="button"
                                            className="shrink-0 rounded-md border border-gray-200 dark:border-gray-700 px-2 py-1 text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5"
                                            onClick={() => setField('settings', { webhook_token: generateToken() })}
                                        >
                                            Regenerate
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Webhook URL</label>
                                    <div className="mt-1 flex items-center gap-2">
                                        <input
                                            type="text"
                                            readOnly
                                            className="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 shadow-sm text-sm text-gray-700 dark:text-gray-200"
                                            value={webhookUrl}
                                        />
                                        <button
                                            type="button"
                                            className="shrink-0 rounded-md border border-gray-200 dark:border-gray-700 px-2 py-1 text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5"
                                            onClick={() => {
                                                if (!webhookUrl) return;
                                                navigator.clipboard?.writeText(webhookUrl);
                                            }}
                                        >
                                            Copy
                                        </button>
                                    </div>
                                    <div className="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                        Send JSON with at least <code className="px-1">email</code> in the body. If no list is selected, contacts will be stored in an auto-created "Webhook Contacts" list.
                                    </div>
                                </div>
                            </div>
                        ) : null}

                        {trigger.startsWith('campaign_') ? (
                            <div>
                                <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Campaign <span className="text-red-500">*</span></label>
                                <select
                                    className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                    value={selected.settings?.campaign_id || ''}
                                    onChange={(e) => setField('settings', { campaign_id: e.target.value || null })}
                                >
                                    <option value="">Select campaign</option>
                                    {(campaigns || []).map((c) => (
                                        <option key={c.id} value={c.id}>{c.name}</option>
                                    ))}
                                </select>
                            </div>
                        ) : null}

                        {['campaign_not_opened', 'campaign_not_replied', 'campaign_opened_not_clicked'].includes(trigger) ? (
                            <div className="grid grid-cols-2 gap-2">
                                <div>
                                    <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Check after <span className="text-red-500">*</span></label>
                                    <input
                                        type="number"
                                        min="1"
                                        className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                        value={Number(selected.settings?.window_value || 24)}
                                        onChange={(e) => setField('settings', { window_value: Number(e.target.value || 0) })}
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Unit <span className="text-red-500">*</span></label>
                                    <select
                                        className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                        value={selected.settings?.window_unit || 'hours'}
                                        onChange={(e) => setField('settings', { window_unit: e.target.value })}
                                    >
                                        <option value="minutes">minutes</option>
                                        <option value="hours">hours</option>
                                        <option value="days">days</option>
                                        <option value="weeks">weeks</option>
                                    </select>
                                </div>
                            </div>
                        ) : null}
                    </div>
                ) : null}

                {selected.type === 'run_campaign' ? (
                    <div className="space-y-3">
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Campaign <span className="text-red-500">*</span></label>
                            <select
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.campaign_id || ''}
                                onChange={(e) => setField('settings', { campaign_id: e.target.value || null })}
                            >
                                <option value="">Select campaign</option>
                                {(campaigns || []).map((c) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                        </div>
                    </div>
                ) : null}

                {selected.type === 'move_subscribers' ? (
                    <div className="space-y-3">
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Move to list <span className="text-red-500">*</span></label>
                            <select
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.target_list_id || ''}
                                onChange={(e) => setField('settings', { target_list_id: e.target.value || null })}
                            >
                                <option value="">Select list</option>
                                {(emailLists || []).map((l) => (
                                    <option key={l.id} value={l.id}>{l.name}</option>
                                ))}
                            </select>
                        </div>
                    </div>
                ) : null}

                {selected.type === 'copy_subscribers' ? (
                    <div className="space-y-3">
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Copy to list <span className="text-red-500">*</span></label>
                            <select
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.target_list_id || ''}
                                onChange={(e) => setField('settings', { target_list_id: e.target.value || null })}
                            >
                                <option value="">Select list</option>
                                {(emailLists || []).map((l) => (
                                    <option key={l.id} value={l.id}>{l.name}</option>
                                ))}
                            </select>
                        </div>
                    </div>
                ) : null}

                {selected.type === 'delay' ? (
                    <div className="space-y-3">
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Delay value <span className="text-red-500">*</span></label>
                            <input
                                type="number"
                                min="0"
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={Number(selected.settings?.delay_value || 0)}
                                onChange={(e) => setField('settings', { delay_value: Number(e.target.value || 0) })}
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Delay unit <span className="text-red-500">*</span></label>
                            <select
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.delay_unit || ''}
                                onChange={(e) => setField('settings', { delay_unit: e.target.value })}
                            >
                                <option value="">Select unit</option>
                                <option value="minutes">Minutes</option>
                                <option value="hours">Hours</option>
                                <option value="days">Days</option>
                                <option value="weeks">Weeks</option>
                            </select>
                        </div>
                    </div>
                ) : null}

                {selected.type === 'email' ? (
                    <div className="space-y-3">
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Subject <span className="text-red-500">*</span></label>
                            <input
                                type="text"
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.subject || ''}
                                onChange={(e) => setField('settings', { subject: e.target.value })}
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Template <span className="text-red-500">*</span></label>
                            <select
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.template_id || ''}
                                onChange={(e) => setField('settings', { template_id: e.target.value || null })}
                            >
                                <option value="">Select template</option>
                                {(templates || []).map((t) => (
                                    <option key={t.id} value={t.id}>{t.name}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Delivery server</label>
                            <select
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.delivery_server_id || ''}
                                onChange={(e) => setField('settings', { delivery_server_id: e.target.value || null })}
                            >
                                <option value="">Default</option>
                                {(deliveryServers || []).map((s) => (
                                    <option key={s.id} value={s.id}>{s.name}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">From name</label>
                            <input
                                type="text"
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.from_name || ''}
                                onChange={(e) => setField('settings', { from_name: e.target.value })}
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">From email</label>
                            <input
                                type="email"
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.from_email || ''}
                                onChange={(e) => setField('settings', { from_email: e.target.value })}
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Reply-to</label>
                            <input
                                type="email"
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.reply_to || ''}
                                onChange={(e) => setField('settings', { reply_to: e.target.value })}
                            />
                        </div>
                        <div className="space-y-2">
                            <label className="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                                <input
                                    type="checkbox"
                                    className="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                    checked={!!selected.settings?.track_opens}
                                    onChange={(e) => setField('settings', { track_opens: e.target.checked })}
                                />
                                Track opens
                            </label>
                            <label className="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                                <input
                                    type="checkbox"
                                    className="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                    checked={!!selected.settings?.track_clicks}
                                    onChange={(e) => setField('settings', { track_clicks: e.target.checked })}
                                />
                                Track clicks
                            </label>
                        </div>
                    </div>
                ) : null}

                {selected.type === 'webhook' ? (
                    <div className="space-y-3">
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">URL</label>
                            <input
                                type="url"
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.url || ''}
                                onChange={(e) => setField('settings', { url: e.target.value })}
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Method</label>
                            <select
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.method || 'POST'}
                                onChange={(e) => setField('settings', { method: e.target.value })}
                            >
                                <option value="POST">POST</option>
                                <option value="GET">GET</option>
                                <option value="PUT">PUT</option>
                                <option value="PATCH">PATCH</option>
                            </select>
                        </div>
                    </div>
                ) : null}

                {selected.type === 'condition' ? (
                    <div className="space-y-3">
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Field</label>
                            <select
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.field || ''}
                                onChange={(e) => setField('settings', { field: e.target.value })}
                            >
                                <option value="">Select</option>
                                <option value="email">Subscriber email</option>
                                <option value="status">Subscriber status</option>
                                <option value="custom_fields.wp_user_id">WP user id (custom)</option>
                                <option value="custom_fields.woo_customer_id">Woo customer id (custom)</option>
                                <option value="payload.order_id">Payload: order id</option>
                                <option value="payload.total">Payload: order total</option>
                                <option value="payload.currency">Payload: currency</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Operator</label>
                            <select
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.operator || 'equals'}
                                onChange={(e) => setField('settings', { operator: e.target.value })}
                            >
                                <option value="equals">Equals</option>
                                <option value="contains">Contains</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-700 dark:text-gray-200">Value</label>
                            <input
                                type="text"
                                className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                value={selected.settings?.value || ''}
                                onChange={(e) => setField('settings', { value: e.target.value })}
                            />
                        </div>
                    </div>
                ) : null}
            </div>
        </div>
    );
}

function NodesPalette({ onAdd }) {
    const items = [
        { type: 'email', label: 'Send Email', subtitle: 'Follow-up step' },
        { type: 'run_campaign', label: 'Run Campaign', subtitle: 'Send an existing campaign' },
        { type: 'move_subscribers', label: 'Move Subscriber(s)', subtitle: 'Move to another list' },
        { type: 'copy_subscribers', label: 'Copy Subscriber(s)', subtitle: 'Copy to another list' },
        { type: 'delay', label: 'Delay', subtitle: 'Wait before next step' },
        { type: 'condition', label: 'Condition', subtitle: 'Branching (basic)' },
        { type: 'webhook', label: 'Webhook', subtitle: 'HTTP request' },
    ];

    return (
        <div>
            <div className="space-y-2">
                <button
                    type="button"
                    className="w-full text-left rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5"
                    onClick={() => onAdd('trigger')}
                    disabled
                    title="Trigger is required"
                >
                    <div className="flex items-start gap-2">
                        <div className="mt-0.5 text-primary-600 dark:text-primary-300"><NodeIcon type="trigger" /></div>
                        <div>
                            <div className="text-sm font-medium text-gray-900 dark:text-gray-100">Trigger</div>
                            <div className="text-xs text-gray-500 dark:text-gray-400">Required</div>
                        </div>
                    </div>
                </button>

                {items.map((it) => (
                    <button
                        key={it.type}
                        type="button"
                        className="w-full text-left rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5"
                        onClick={() => onAdd(it.type)}
                    >
                        <div className="flex items-start gap-2">
                            <div className="mt-0.5 text-primary-600 dark:text-primary-300"><NodeIcon type={it.type} /></div>
                            <div>
                                <div className="text-sm font-medium text-gray-900 dark:text-gray-100">{it.label}</div>
                                <div className="text-xs text-gray-500 dark:text-gray-400">{it.subtitle}</div>
                            </div>
                        </div>
                    </button>
                ))}
            </div>

            <div className="mt-4 text-xs text-gray-500 dark:text-gray-400">Use the + buttons in the flow to add steps.</div>
        </div>
    );
}

function AutomationBuilderApp({ initialGraph, emailLists, campaigns, templates, deliveryServers, hideAttribution, automationId }) {
    const initial = useMemo(() => {
        const ensured = ensureTrigger(initialGraph || {});
        return simpleVerticalLayout(ensured);
    }, [initialGraph]);

    const graphNodeMetaById = useMemo(() => {
        const m = new Map();
        (initialGraph?.nodes || []).forEach((n) => {
            if (n?.id) m.set(String(n.id), n);
        });
        return m;
    }, [initialGraph]);

    const [selectedId, setSelectedId] = useState('trigger_1');

    const hasExplicitSelectionRef = useRef(false);

    const hasManualLayoutRef = useRef(false);

    const [rightTab, setRightTab] = useState('nodes');

    const sidebarRef = useRef(null);
    const [canvasHeight, setCanvasHeight] = useState(520);
    const maxSidebarHeightRef = useRef(520);
    const fullHeightModeRef = useRef(false);

    const [needsLayout, setNeedsLayout] = useState(false);

    const [insertMenu, setInsertMenu] = useState({ open: false, x: 0, y: 0, fromId: null, branch: null });

    const callbacks = useMemo(
        () => ({
            onSelect: (id) => {
                hasExplicitSelectionRef.current = true;
                setSelectedId(id);
            },
            onInsert: (fromId, branch, point) => {
                if (fromId) {
                    hasExplicitSelectionRef.current = true;
                    setSelectedId(fromId);
                }
                const p = point || { x: 0, y: 0 };
                setInsertMenu({ open: true, x: Number(p.x) || 0, y: Number(p.y) || 0, fromId, branch: branch || null });
            },
        }),
        []
    );

    const [nodes, setNodes, onNodesChange] = useNodesState(toFlowNodes(initial.nodes, callbacks));
    const [edges, setEdges, onEdgesChange] = useEdgesState(toFlowEdges(initial.edges));

    const onNodesChangeTracked = useCallback(
        (changes) => {
            if (
                Array.isArray(changes) &&
                changes.some((c) => c && c.type === 'position' && (c.dragging === true || c.dragging === false))
            ) {
                hasManualLayoutRef.current = true;
            }
            onNodesChange(changes);
        },
        [onNodesChange]
    );

    const selectedNode = useMemo(() => {
        const n = nodes.find((x) => x.id === selectedId);
        if (!n) return null;
        return {
            id: n.id,
            type: n.data?.type,
            label: n.data?.label,
            subtitle: n.data?.subtitle,
            settings: n.data?.settings || {},
        };
    }, [nodes, selectedId]);

    const updateSelected = useCallback(
        (updater) => {
            setNodes((nds) =>
                nds.map((n) => {
                    if (n.id !== selectedId) return n;
                    const cur = {
                        id: n.id,
                        type: n.data?.type,
                        label: n.data?.label,
                        subtitle: n.data?.subtitle,
                        settings: n.data?.settings || {},
                    };
                    let next = updater(cur);

                    next = {
                        ...next,
                        subtitle: syncSubtitle(next, { emailLists, campaigns }),
                    };
                    return {
                        ...n,
                        data: {
                            ...n.data,
                            type: next.type,
                            label: next.label,
                            subtitle: next.subtitle,
                            settings: next.settings,
                        },
                    };
                })
            );
        },
        [selectedId, setNodes, emailLists, campaigns]
    );

    const hiddenInputRef = useRef(null);

    useEffect(() => {
        hiddenInputRef.current = document.querySelector('input[name="graph_json"]');
    }, []);

    const syncGraphJson = useCallback(
        (nextNodes, nextEdges) => {
            const meta = new Map();
            nextNodes.forEach((n) => {
                meta.set(n.id, {
                    type: n.data?.type,
                    label: n.data?.label,
                    subtitle: n.data?.subtitle,
                    settings: n.data?.settings || {},
                });
            });
            const g = fromFlow(nextNodes, nextEdges, meta);
            if (hiddenInputRef.current) {
                hiddenInputRef.current.value = JSON.stringify(g);
            }
        },
        []
    );

    useEffect(() => {
        syncGraphJson(nodes, edges);
    }, [nodes, edges, syncGraphJson]);

    useEffect(() => {
        const mountEl = document.getElementById('automation-builder-react');
        const wantsFullHeight = mountEl && ['true', '1', 'yes'].includes(String(mountEl.getAttribute('data-full-height') || '').toLowerCase());
        fullHeightModeRef.current = !!wantsFullHeight;

        const el = sidebarRef.current;
        if (!el) return;

        const update = () => {
            if (fullHeightModeRef.current) {
                // Full-screen mode: compute available height based on viewport and the mount container.
                try {
                    if (!mountEl) return;
                    const rect = mountEl.getBoundingClientRect();
                    const available = Math.max(240, Math.floor(window.innerHeight - rect.top - 16));
                    setCanvasHeight(available);
                } catch {
                    // ignore
                }
                return;
            }

            const h = el.getBoundingClientRect().height;
            if (!Number.isFinite(h) || h <= 0) return;

            const next = Math.round(h);
            if (next > maxSidebarHeightRef.current) {
                maxSidebarHeightRef.current = next;
                setCanvasHeight(next);
            }
        };

        update();

        const ro = new ResizeObserver(() => update());
        ro.observe(el);
        window.addEventListener('resize', update);
        return () => {
            ro.disconnect();
            window.removeEventListener('resize', update);
        };
    }, [rightTab]);

    useEffect(() => {
        if (!needsLayout) return;
        if (hasManualLayoutRef.current) {
            setNeedsLayout(false);
            return;
        }

        const meta = new Map();
        nodes.forEach((n) => {
            meta.set(n.id, {
                type: n.data?.type,
                label: n.data?.label,
                subtitle: n.data?.subtitle,
                settings: n.data?.settings || {},
            });
        });

        const g = fromFlow(nodes, edges, meta);
        const laid = simpleVerticalLayout(g);
        const posById = new Map((laid.nodes || []).map((n) => [String(n.id), { x: Number(n.x) || 0, y: Number(n.y) || 0 }]));

        const nextNodes = nodes.map((n) => {
            const p = posById.get(n.id);
            if (!p) return n;
            if (n.position?.x === p.x && n.position?.y === p.y) return n;
            return { ...n, position: p };
        });

        setNeedsLayout(false);
        setNodes(nextNodes);
    }, [needsLayout, nodes, edges, setNodes]);

    const onConnect = useCallback(
        (params) => {
            setEdges((eds) =>
                addEdge(
                    {
                        ...params,
                        id: uid('edge'),
                        type: 'smoothstep',
                        markerEnd: { type: MarkerType.ArrowClosed, width: 16, height: 16, color: 'rgba(59,130,246,0.9)' },
                        style: { stroke: 'rgba(59,130,246,0.9)', strokeWidth: 2 },
                    },
                    eds
                )
            );
        },
        [setEdges]
    );

    const doInsert = useCallback(
        (type, fromId, branch) => {
            if (!fromId) return;

            const INSERT_GAP_Y = 200;

            // Create node
            const base = {
                trigger: { label: 'Trigger', subtitle: 'Start', settings: { list_id: null, campaign_id: null, trigger: '', window_value: 24, window_unit: 'hours' } },
                email: { label: 'Send Email', subtitle: 'Email action', settings: { subject: '', template_id: null, delivery_server_id: null, from_name: '', from_email: '', reply_to: '', track_opens: true, track_clicks: true } },
                run_campaign: { label: 'Run Campaign', subtitle: 'Send campaign', settings: { campaign_id: null } },
                move_subscribers: { label: 'Move Subscriber(s)', subtitle: 'Move to list', settings: { target_list_id: null } },
                copy_subscribers: { label: 'Copy Subscriber(s)', subtitle: 'Copy to list', settings: { target_list_id: null } },
                delay: { label: 'Delay', subtitle: 'Wait time', settings: { delay_value: 0, delay_unit: 'hours' } },
                webhook: { label: 'Webhook', subtitle: 'HTTP request', settings: { url: '', method: 'POST' } },
                condition: { label: 'Condition', subtitle: 'Branch', settings: { field: '', operator: 'equals', value: '' } },
            };
            const meta = base[type] || { label: 'Action', subtitle: '', settings: {} };

            const newId = uid(type);
            const fromNode = nodes.find((n) => n.id === fromId);
            const fromPos = fromNode?.position || { x: 0, y: 0 };

            const newNode = {
                id: newId,
                type: 'base',
                position: { x: fromPos.x, y: fromPos.y + INSERT_GAP_Y },
                data: {
                    id: newId,
                    type,
                    label: meta.label,
                    subtitle: syncSubtitle({ id: newId, type, label: meta.label, subtitle: meta.subtitle, settings: meta.settings }, { emailLists, campaigns }),
                    settings: meta.settings,
                    onSelect: callbacks.onSelect,
                    onInsert: callbacks.onInsert,
                },
            };

            const wantBranch = branch || undefined;
            const nextNodes = [...nodes, newNode];

            const existingOutIdx = edges.findIndex((e) => {
                if (e.source !== fromId) return false;
                const b = e.data?.branch || undefined;
                if (!wantBranch) return !b;
                return b === wantBranch;
            });

            let nextEdges = [...edges];
            if (existingOutIdx >= 0) {
                const existing = edges[existingOutIdx];
                const oldTarget = existing.target;

                if (hasManualLayoutRef.current) {
                    const targetNode = nodes.find((n) => n.id === oldTarget);
                    const targetPos = targetNode?.position || { x: fromPos.x, y: fromPos.y + (INSERT_GAP_Y * 2) };

                    const currentSpan = (Number(targetPos.y) || 0) - (Number(fromPos.y) || 0);
                    const requiredSpan = INSERT_GAP_Y * 2;
                    const shiftDownBy = Math.max(0, requiredSpan - currentSpan);

                    if (shiftDownBy > 0) {
                        const downstreamIds = collectDownstreamIds(oldTarget, edges);
                        for (let i = 0; i < nextNodes.length; i++) {
                            const n = nextNodes[i];
                            if (!downstreamIds.has(String(n.id))) continue;
                            nextNodes[i] = {
                                ...n,
                                position: {
                                    ...(n.position || { x: 0, y: 0 }),
                                    y: (Number(n.position?.y) || 0) + shiftDownBy,
                                },
                            };
                        }
                    }

                    newNode.position = { x: Number(fromPos.x) || 0, y: (Number(fromPos.y) || 0) + INSERT_GAP_Y };
                }

                nextEdges[existingOutIdx] = {
                    ...existing,
                    target: newId,
                    sourceHandle: wantBranch === 'yes' ? 'yes' : wantBranch === 'no' ? 'no' : existing.sourceHandle,
                    data: { ...(existing.data || {}), branch: wantBranch },
                };

                nextEdges.push({
                    id: uid('edge'),
                    source: newId,
                    target: oldTarget,
                    type: 'smoothstep',
                    markerEnd: { type: MarkerType.ArrowClosed, width: 16, height: 16, color: 'rgba(59,130,246,0.9)' },
                    style: { stroke: 'rgba(59,130,246,0.9)', strokeWidth: 2 },
                    data: {},
                });
            } else {
                nextEdges.push({
                    id: uid('edge'),
                    source: fromId,
                    target: newId,
                    sourceHandle: wantBranch === 'yes' ? 'yes' : wantBranch === 'no' ? 'no' : undefined,
                    type: 'smoothstep',
                    markerEnd: { type: MarkerType.ArrowClosed, width: 16, height: 16, color: 'rgba(59,130,246,0.9)' },
                    style: { stroke: 'rgba(59,130,246,0.9)', strokeWidth: 2 },
                    data: { branch: wantBranch },
                });
            }

            setNodes(nextNodes);
            setEdges(nextEdges);
            if (!hasManualLayoutRef.current) {
                setNeedsLayout(true);
            }

            setSelectedId(newId);
            setRightTab('settings');

            pendingCenterOnIdRef.current = newId;
        },
        [nodes, edges, setNodes, setEdges, callbacks, emailLists, campaigns]
    );

    const addAfterSelected = useCallback(
        (type) => {
            let fromId = selectedId || 'trigger_1';

            if (!hasExplicitSelectionRef.current && fromId === 'trigger_1') {
                const sources = new Set((edges || []).map((e) => String(e.source)));
                const triggerX = Number(nodes.find((n) => n.id === 'trigger_1')?.position?.x) || 0;
                const endCandidates = (nodes || []).filter((n) => n.id !== 'trigger_1' && !sources.has(String(n.id)));

                const pickDefaultAppendNode = (arr) => {
                    const xs = [...(arr || [])];
                    xs.sort((a, b) => {
                        const ay = Number(a.position?.y) || 0;
                        const by = Number(b.position?.y) || 0;
                        if (ay !== by) return by - ay;

                        const ax = Number(a.position?.x) || 0;
                        const bx = Number(b.position?.x) || 0;
                        const ad = Math.abs(ax - triggerX);
                        const bd = Math.abs(bx - triggerX);
                        if (ad !== bd) return ad - bd;

                        return ax - bx;
                    });
                    return xs[0];
                };

                const picked = endCandidates.length
                    ? pickDefaultAppendNode(endCandidates)
                    : pickDefaultAppendNode((nodes || []).filter((n) => n.id !== 'trigger_1'));

                if (picked?.id) {
                    fromId = picked.id;
                }
            }

            doInsert(type, fromId, null);
        },
        [doInsert, selectedId, nodes, edges]
    );

    const insertItems = useMemo(
        () => [
            { type: 'email', label: 'Send Email' },
            { type: 'run_campaign', label: 'Run Campaign' },
            { type: 'move_subscribers', label: 'Move Subscriber(s)' },
            { type: 'copy_subscribers', label: 'Copy Subscriber(s)' },
            { type: 'delay', label: 'Delay' },
            { type: 'condition', label: 'Condition' },
            { type: 'webhook', label: 'Webhook' },
        ],
        []
    );

    const closeInsertMenu = useCallback(() => {
        setInsertMenu({ open: false, x: 0, y: 0, fromId: null, branch: null });
    }, []);

    const deleteSelected = useCallback(() => {
        if (!selectedId || selectedId === 'trigger_1') {
            return;
        }

        const id = selectedId;

        const incoming = (edges || []).filter((e) => e.target === id);
        const outgoing = (edges || []).filter((e) => e.source === id);

        const nextEdges = (edges || []).filter((e) => e.source !== id && e.target !== id);

        // If this node sits in a simple chain (1 incoming, 1 outgoing), bypass it so the flow stays connected.
        if (incoming.length === 1 && outgoing.length === 1) {
            const inE = incoming[0];
            const outE = outgoing[0];

            const source = inE.source;
            const target = outE.target;
            const branch = inE.data?.branch || undefined;

            if (source && target && source !== id && target !== id && source !== target) {
                const already = nextEdges.some((e) => {
                    if (e.source !== source) return false;
                    if (e.target !== target) return false;
                    const b = e.data?.branch || undefined;
                    return (b || undefined) === (branch || undefined);
                });

                if (!already) {
                    nextEdges.push({
                        id: uid('edge'),
                        source,
                        target,
                        sourceHandle: branch === 'yes' ? 'yes' : branch === 'no' ? 'no' : undefined,
                        type: 'smoothstep',
                        markerEnd: { type: MarkerType.ArrowClosed, width: 16, height: 16, color: 'rgba(59,130,246,0.9)' },
                        style: { stroke: 'rgba(59,130,246,0.9)', strokeWidth: 2 },
                        data: { branch },
                    });
                }
            }
        }

        setEdges(nextEdges);
        setNodes((nds) => nds.filter((n) => n.id !== id));
        setSelectedId('trigger_1');
        setRightTab('nodes');
        if (!hasManualLayoutRef.current) {
            setNeedsLayout(true);
        }
    }, [selectedId, setEdges, setNodes, edges]);

    useEffect(() => {
        if (!insertMenu.open) return;

        const onKeyDown = (e) => {
            if (e.key === 'Escape') {
                closeInsertMenu();
            }
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [insertMenu.open, closeInsertMenu]);

    useEffect(() => {
        const onKeyDown = (e) => {
            if (e.key !== 'Backspace' && e.key !== 'Delete') {
                return;
            }

            const el = e.target;
            const tag = (el?.tagName || '').toLowerCase();
            const editable = el?.isContentEditable;
            if (editable || tag === 'input' || tag === 'textarea' || tag === 'select') {
                return;
            }

            e.preventDefault();
            deleteSelected();
        };

        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [deleteSelected]);

    const { setViewport, getZoom, setCenter } = useReactFlow();
    const didInitViewportRef = useRef(false);
    useEffect(() => {
        if (didInitViewportRef.current) return;
        didInitViewportRef.current = true;

        const t = setTimeout(() => {
            const trigger = nodes.find((n) => n.id === 'trigger_1');
            const p = trigger?.position || { x: 0, y: 0 };
            try {
                setViewport({ x: 0, y: 0, zoom: 0.5 }, { duration: 0 });
                setCenter(Number(p.x) || 0, Number(p.y) || 0, { zoom: 0.5, duration: 0 });
            } catch {
                // ignore
            }
        }, 50);

        return () => clearTimeout(t);
    }, [nodes, setViewport, setCenter]);

    const pendingCenterOnIdRef = useRef(null);
    useEffect(() => {
        const targetId = pendingCenterOnIdRef.current;
        if (!targetId) return;

        const n = nodes.find((x) => x.id === targetId);
        if (!n) return;

        pendingCenterOnIdRef.current = null;
        const p = n.position || { x: 0, y: 0 };
        const z = Number(getZoom?.()) || 0.5;
        try {
            setCenter(Number(p.x) || 0, Number(p.y) || 0, { zoom: z, duration: 250 });
        } catch {
            // ignore
        }
    }, [nodes, getZoom, setCenter]);

    return (
        <div className="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div className="lg:col-span-9">
                <div className="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
                    <div className="relative w-full" style={{ height: canvasHeight }}>
                        {insertMenu.open ? (
                            <>
                                <div
                                    className="fixed inset-0 z-40"
                                    onClick={() => closeInsertMenu()}
                                />
                                <div
                                    className="fixed z-50"
                                    style={{ left: insertMenu.x, top: insertMenu.y }}
                                >
                                    <div className="w-48 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg p-2">
                                        <div className="text-[11px] font-semibold text-gray-500 dark:text-gray-400 px-2 py-1">Add step</div>
                                        {insertItems.map((it) => (
                                            <button
                                                key={it.type}
                                                type="button"
                                                className="w-full text-left rounded-md px-2 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5"
                                                onClick={() => {
                                                    const fromId = insertMenu.fromId;
                                                    const branch = insertMenu.branch;
                                                    closeInsertMenu();
                                                    doInsert(it.type, fromId, branch);
                                                }}
                                            >
                                                <span className="inline-flex items-center gap-2">
                                                    <span className="text-primary-600 dark:text-primary-300"><NodeIcon type={it.type} /></span>
                                                    <span>{it.label}</span>
                                                </span>
                                            </button>
                                        ))}
                                        <div className="mt-1 px-2">
                                            <button
                                                type="button"
                                                className="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-200"
                                                onClick={() => closeInsertMenu()}
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </>
                        ) : null}
                        <ReactFlow
                            nodes={nodes}
                            edges={edges}
                            onNodesChange={onNodesChangeTracked}
                            onEdgesChange={onEdgesChange}
                            onConnect={onConnect}
                            nodeTypes={nodeTypes}
                            onNodeClick={(_, node) => {
                                hasExplicitSelectionRef.current = true;
                                setSelectedId(node.id);
                                setRightTab('settings');
                            }}
                            proOptions={hideAttribution ? { hideAttribution: true } : undefined}
                        >
                            <Background gap={16} size={1} color="rgba(148, 163, 184, 0.55)" />
                            <MiniMap pannable zoomable />
                            <Controls />
                        </ReactFlow>
                    </div>
                </div>
            </div>

            <div className="lg:col-span-3">
                <div
                    ref={sidebarRef}
                    className="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden"
                    style={{ minHeight: canvasHeight }}
                >
                    <div className="flex items-center border-b border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            className={`flex-1 px-3 py-2 text-sm font-medium ${rightTab === 'nodes' ? 'text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-white/5' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5'}`}
                            onClick={() => setRightTab('nodes')}
                        >
                            Nodes
                        </button>
                        <button
                            type="button"
                            className={`flex-1 px-3 py-2 text-sm font-medium ${rightTab === 'settings' ? 'text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-white/5' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5'}`}
                            onClick={() => setRightTab('settings')}
                        >
                            Settings
                        </button>
                    </div>

                    <div className="p-3">
                        {rightTab === 'nodes' ? (
                            <NodesPalette
                                onAdd={(type) => {
                                    addAfterSelected(type);
                                    setRightTab('settings');
                                }}
                            />
                        ) : (
                            <SettingsPanel
                                selected={selectedNode}
                                setSelected={setSelectedId}
                                emailLists={emailLists}
                                campaigns={campaigns}
                                templates={templates}
                                deliveryServers={deliveryServers}
                                onUpdateSelected={updateSelected}
                                automationId={automationId}
                                onDeleteSelected={deleteSelected}
                            />
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

function Mount() {
    const el = document.getElementById('automation-builder-react');
    if (!el) return;

    if (mountedEl === el && root) {
        return;
    }

    if (root && mountedEl && mountedEl !== el) {
        try {
            root.unmount();
        } catch (_) {
            //
        }
        root = null;
        mountedEl = null;
    }

    const initialGraph = safeJsonParse(el.getAttribute('data-initial-graph'), {});
    const emailLists = safeJsonParse(el.getAttribute('data-email-lists'), []);
    const campaigns = safeJsonParse(el.getAttribute('data-campaigns'), []);
    const templates = safeJsonParse(el.getAttribute('data-templates'), []);
    const deliveryServers = safeJsonParse(el.getAttribute('data-delivery-servers'), []);
    const automationId = el.getAttribute('data-automation-id') || '';
    const hideAttribution = ['true', '1', 'yes'].includes(
        String(el.getAttribute('data-hide-attribution') || '').toLowerCase()
    );

    root = createRoot(el);
    mountedEl = el;
    root.render(
        <ReactFlowProvider>
            <AutomationBuilderApp
                initialGraph={initialGraph}
                emailLists={emailLists}
                campaigns={campaigns}
                templates={templates}
                deliveryServers={deliveryServers}
                hideAttribution={hideAttribution}
                automationId={automationId}
            />
        </ReactFlowProvider>
    );
}

let root = null;
let mountedEl = null;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', Mount);
} else {
    Mount();
}

document.addEventListener('turbo:load', Mount);
document.addEventListener('turbo:render', Mount);
document.addEventListener('turbo:before-cache', () => {
    if (!root) return;
    try {
        root.unmount();
    } catch (_) {
        //
    }
    root = null;
    mountedEl = null;
});
