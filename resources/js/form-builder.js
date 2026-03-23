class MailZenFormBuilder {
    constructor() {
        this.fields = [];
        this.selectedField = null;
        this.fieldIdCounter = 0;
        this.isPreviewMode = false;
        
        this.init();
    }

    init() {
        this.setupDragAndDrop();
        this.setupEventListeners();
        this.loadExistingData();
    }

    setupDragAndDrop() {
        const fieldTypes = document.querySelectorAll('[data-field-type]');
        const dropZone = document.getElementById('drop-zone');

        fieldTypes.forEach(field => {
            field.draggable = true;
            
            field.addEventListener('dragstart', (e) => {
                e.dataTransfer.effectAllowed = 'copy';
                e.dataTransfer.setData('fieldType', field.dataset.fieldType);
                field.classList.add('dragging');
            });

            field.addEventListener('dragend', (e) => {
                field.classList.remove('dragging');
            });
        });

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            dropZone.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', (e) => {
            if (e.target === dropZone) {
                dropZone.classList.remove('drag-over');
            }
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            
            const fieldType = e.dataTransfer.getData('fieldType');
            if (fieldType) {
                this.addField(fieldType);
            }
        });
    }

    setupEventListeners() {
        // Save button
        document.getElementById('btn-save').addEventListener('click', () => {
            this.saveForm();
        });

        // Preview button
        document.getElementById('btn-preview').addEventListener('click', () => {
            this.togglePreview();
        });

        // Close preview
        document.getElementById('preview-overlay').addEventListener('click', () => {
            this.closePreview();
        });

        // Popup settings
        if (window.formType === 'popup') {
            ['popup_width', 'popup_height', 'popup_bg_color', 'popup_overlay_color'].forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.addEventListener('change', () => {
                        this.updatePopupSettings();
                    });
                }
            });
        }
    }

    addField(type) {
        const field = {
            id: `field_${++this.fieldIdCounter}`,
            type: type,
            label: this.getDefaultLabel(type),
            name: `${type}_${this.fieldIdCounter}`,
            required: false,
            placeholder: '',
            options: type === 'select' ? ['Option 1', 'Option 2', 'Option 3'] : [],
            className: 'form-control'
        };

        this.fields.push(field);
        this.renderForm();
        this.selectField(field.id);
    }

    getDefaultLabel(type) {
        const labels = {
            text: 'Text Input',
            email: 'Email',
            textarea: 'Message',
            select: 'Select Option',
            checkbox: 'I agree',
            radio: 'Choice',
            submit: 'Submit'
        };
        return labels[type] || 'Field';
    }

    renderForm() {
        const dropZone = document.getElementById('drop-zone');
        
        if (this.fields.length === 0) {
            dropZone.innerHTML = `
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p>Drag form fields here to start building</p>
                </div>
            `;
            return;
        }

        let formHTML = '<form class="space-y-4">';
        
        this.fields.forEach(field => {
            formHTML += this.renderField(field);
        });

        formHTML += '</form>';
        
        dropZone.innerHTML = formHTML;
        dropZone.classList.remove('drag-over');

        // Add click handlers to fields
        dropZone.querySelectorAll('.form-field-element').forEach(el => {
            el.addEventListener('click', () => {
                this.selectField(el.dataset.fieldId);
            });
        });
    }

    renderField(field) {
        const fieldHTML = `
            <div class="form-field-element ${this.selectedField === field.id ? 'selected' : ''}" data-field-id="${field.id}">
                ${this.getFieldHTML(field)}
            </div>
        `;
        return fieldHTML;
    }

    getFieldHTML(field) {
        switch (field.type) {
            case 'text':
            case 'email':
                return `
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            ${field.label} ${field.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <input type="${field.type}" 
                               name="${field.name}" 
                               placeholder="${field.placeholder}"
                               ${field.required ? 'required' : ''}
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                `;
            
            case 'textarea':
                return `
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            ${field.label} ${field.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <textarea name="${field.name}" 
                                  placeholder="${field.placeholder}"
                                  rows="4"
                                  ${field.required ? 'required' : ''}
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                `;
            
            case 'select':
                return `
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            ${field.label} ${field.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <select name="${field.name}" 
                                ${field.required ? 'required' : ''}
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Select an option</option>
                            ${field.options.map(opt => `<option value="${opt}">${opt}</option>`).join('')}
                        </select>
                    </div>
                `;
            
            case 'checkbox':
                return `
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="${field.name}" 
                                   value="1"
                                   ${field.required ? 'required' : ''}
                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-700">${field.label}</span>
                            ${field.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                    </div>
                `;
            
            case 'radio':
                return `
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ${field.label} ${field.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        ${field.options.map((opt, index) => `
                            <label class="flex items-center mb-1">
                                <input type="radio" 
                                       name="${field.name}" 
                                       value="${opt}"
                                       ${field.required && index === 0 ? 'required' : ''}
                                       class="border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="ml-2 text-sm text-gray-700">${opt}</span>
                            </label>
                        `).join('')}
                    </div>
                `;
            
            case 'submit':
                return `
                    <div class="mb-4">
                        <button type="submit" 
                                class="w-full px-4 py-2 text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            ${field.label}
                        </button>
                    </div>
                `;
            
            default:
                return '';
        }
    }

    selectField(fieldId) {
        this.selectedField = fieldId;
        const field = this.fields.find(f => f.id === fieldId);
        
        if (field) {
            this.renderForm();
            this.showProperties(field);
        }
    }

    showProperties(field) {
        const panel = document.getElementById('properties-panel');
        
        let propertiesHTML = `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Field Label</label>
                    <input type="text" 
                           id="prop-label" 
                           value="${field.label}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Field Name</label>
                    <input type="text" 
                           id="prop-name" 
                           value="${field.name}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
        `;

        if (['text', 'email', 'textarea'].includes(field.type)) {
            propertiesHTML += `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Placeholder</label>
                    <input type="text" 
                           id="prop-placeholder" 
                           value="${field.placeholder}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            `;
        }

        if (['select', 'radio'].includes(field.type)) {
            propertiesHTML += `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Options (one per line)</label>
                    <textarea id="prop-options" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md">${field.options.join('\n')}</textarea>
                </div>
            `;
        }

        propertiesHTML += `
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               id="prop-required" 
                               ${field.required ? 'checked' : ''}
                               class="rounded border-gray-300 text-primary-600">
                        <span class="ml-2 text-sm text-gray-700">Required</span>
                    </label>
                </div>
                <div class="pt-4 border-t">
                    <button type="button" 
                            onclick="formBuilder.removeField('${field.id}')"
                            class="w-full px-3 py-2 text-sm text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700">
                        Remove Field
                    </button>
                </div>
            </div>
        `;

        panel.innerHTML = propertiesHTML;

        // Add change listeners
        document.getElementById('prop-label').addEventListener('input', (e) => {
            field.label = e.target.value;
            this.renderForm();
        });

        document.getElementById('prop-name').addEventListener('input', (e) => {
            field.name = e.target.value;
        });

        if (document.getElementById('prop-placeholder')) {
            document.getElementById('prop-placeholder').addEventListener('input', (e) => {
                field.placeholder = e.target.value;
                this.renderForm();
            });
        }

        if (document.getElementById('prop-options')) {
            document.getElementById('prop-options').addEventListener('input', (e) => {
                field.options = e.target.value.split('\n').filter(opt => opt.trim());
                this.renderForm();
            });
        }

        document.getElementById('prop-required').addEventListener('change', (e) => {
            field.required = e.target.checked;
            this.renderForm();
        });
    }

    removeField(fieldId) {
        this.fields = this.fields.filter(f => f.id !== fieldId);
        this.selectedField = null;
        this.renderForm();
        document.getElementById('properties-panel').innerHTML = '<p class="text-sm text-gray-500">Select a field to edit its properties</p>';
    }

    generateHTML() {
        if (this.fields.length === 0) {
            return '';
        }

        let html = '<form class="space-y-4" method="POST">';
        
        this.fields.forEach(field => {
            html += this.getFieldHTML(field);
        });

        html += '</form>';
        
        return html;
    }

    saveForm() {
        const html = this.generateHTML();
        const builderData = JSON.stringify(this.fields);
        
        document.getElementById('html_content').value = html;
        document.getElementById('builder_data').value = builderData;
        
        // Update popup settings if applicable
        if (window.formType === 'popup') {
            document.getElementById('settings_popup_width').value = document.getElementById('popup_width').value;
            document.getElementById('settings_popup_height').value = document.getElementById('popup_height').value;
            document.getElementById('settings_popup_bg_color').value = document.getElementById('popup_bg_color').value;
            document.getElementById('settings_popup_overlay_color').value = document.getElementById('popup_overlay_color').value;
        }
        
        document.getElementById('form-builder-form').submit();
    }

    togglePreview() {
        if (this.isPreviewMode) {
            this.closePreview();
        } else {
            this.showPreview();
        }
    }

    showPreview() {
        const modal = document.getElementById('preview-modal');
        const overlay = document.getElementById('preview-overlay');
        const content = document.getElementById('preview-content');
        
        const html = this.generateHTML();
        
        if (window.formType === 'popup') {
            const width = document.getElementById('popup_width').value;
            const height = document.getElementById('popup_height').value;
            const bgColor = document.getElementById('popup_bg_color').value;
            const overlayColor = document.getElementById('popup_overlay_color').value;
            
            overlay.style.backgroundColor = this.hexToRgba(overlayColor, 0.5);
            content.style.width = width + 'px';
            content.style.height = height + 'px';
            content.style.backgroundColor = bgColor;
            content.innerHTML = `
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Preview</h2>
                    ${html}
                </div>
                <button onclick="formBuilder.closePreview()" 
                        class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
        } else {
            content.style.width = '600px';
            content.style.maxWidth = '90%';
            content.innerHTML = `
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Preview</h2>
                    ${html}
                    <button onclick="formBuilder.closePreview()" 
                            class="mt-4 px-4 py-2 text-sm text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300">
                        Close Preview
                    </button>
                </div>
            `;
        }
        
        modal.classList.remove('hidden');
        this.isPreviewMode = true;
    }

    closePreview() {
        document.getElementById('preview-modal').classList.add('hidden');
        this.isPreviewMode = false;
    }

    hexToRgba(hex, alpha) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    loadExistingData() {
        if (window.formBuilderData && Array.isArray(window.formBuilderData)) {
            this.fields = window.formBuilderData;
            this.fieldIdCounter = this.fields.length;
            this.renderForm();
        }
    }

    updatePopupSettings() {
        // This is called when popup settings change
        // Preview will use the updated values when shown
    }
}

// Initialize the form builder
const formBuilder = new MailZenFormBuilder();
