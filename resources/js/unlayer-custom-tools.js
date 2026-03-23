// Unlayer custom tools script
// This script will be injected into the Unlayer iframe

console.error('[MailZen Unlayer customJS] Script loaded at', new Date().toISOString());
document.body.style.background = 'rgba(255,0,0,0.3)';

// Wait for unlayer to be available
function waitForUnlayer() {
    if (window.unlayer) {
        console.log('[MailZen Unlayer customJS] Unlayer found, registering tools');
        registerMyTools();
    } else {
        console.log('[MailZen Unlayer customJS] Unlayer not found, retrying...');
        setTimeout(waitForUnlayer, 100);
    }
}

function registerMyTools() {
    // Register a simple test tool first
    try {
        window.unlayer.registerTool({
            name: 'test_tool',
            label: 'Test Tool',
            icon: 'fa-smile',
            supportedDisplayModes: ['web', 'email', 'popup'],
            options: {},
            values: {},
            renderer: {
                Viewer: window.unlayer.createViewer({
                    render: function() {
                        return '<div style="padding:20px;background:yellow;border:2px solid red;">TEST TOOL WORKS!</div>';
                    }
                }),
                exporters: {
                    web: function() { return '<div>TEST TOOL</div>'; },
                    email: function() { return '<div>TEST TOOL</div>'; },
                    popup: function() { return '<div>TEST TOOL</div>'; }
                },
                head: { css: function() {}, js: function() {} }
            }
        });
        console.log('[MailZen Unlayer customJS] ✓ Test tool registered!');
        
        // Also try to register the form tool
        window.unlayer.registerTool({
            name: 'mp_form',
            label: 'Form',
            icon: 'fa-wpforms',
            supportedDisplayModes: ['web', 'email', 'popup'],
            options: {},
            values: {},
            renderer: {
                Viewer: window.unlayer.createViewer({
                    render: function() {
                        return '<div style="padding:16px;border:1px dashed #cbd5e1;border-radius:12px;font-size:13px;">Form Container</div>';
                    }
                }),
                exporters: {
                    web: function() { return '<div style="padding:16px;border:1px solid #e5e7eb;border-radius:12px;">{{fields}}{{gdpr}}{{submit}}</div>'; },
                    email: function() { return '<div style="padding:16px;border:1px solid #e5e7eb;border-radius:12px;">{{fields}}{{gdpr}}{{submit}}</div>'; },
                    popup: function() { return '<div style="padding:16px;border:1px solid #e5e7eb;border-radius:12px;">{{fields}}{{gdpr}}{{submit}}</div>'; }
                },
                head: { css: function() {}, js: function() {} }
            }
        });
        console.log('[MailZen Unlayer customJS] ✓ Form tool registered!');
    } catch (e) {
        console.error('[MailZen Unlayer customJS] Failed to register tool:', e);
    }
}

// Start checking
waitForUnlayer();
