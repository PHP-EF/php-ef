class DynamicSelect {

    constructor(element, options = {}) {
        let defaults = {
            placeholder: 'Select an option',
            columns: 1,
            name: '',
            width: '',
            height: '',
            data: [],
            onChange: function() {}
        };
        this.options = Object.assign(defaults, options);
        this.selectElement = typeof element === 'string' ? document.querySelector(element) : element;
        for(const prop in this.selectElement.dataset) {
            if (this.options[prop] !== undefined) {
                this.options[prop] = this.selectElement.dataset[prop];
            }
        }
        this.name = this.selectElement.getAttribute('name') ? this.selectElement.getAttribute('name') : 'dynamic-select-' + Math.floor(Math.random() * 1000000);
        if (!this.options.data.length) {
            let options = this.selectElement.querySelectorAll('option');
            for (let i = 0; i < options.length; i++) {
                this.options.data.push({
                    value: options[i].value,
                    text: options[i].innerHTML,
                    img: options[i].getAttribute('data-img'),
                    selected: options[i].selected,
                    html: options[i].getAttribute('data-html'),
                    imgWidth: options[i].getAttribute('data-img-width'),
                    imgHeight: options[i].getAttribute('data-img-height'),
                    type: options[i].getAttribute('data-type')
                });
            }
        }
        this.element = this._template();
        this.selectElement.replaceWith(this.element);
        this._updateSelected();
        this._eventHandlers();
        this.isDisabled = this.selectElement.disabled;
        this._updateDisabledState();
    }

    _template() {
        let optionsHTML = '';
        for (let i = 0; i < this.data.length; i++) {
            let optionWidth = 100 / this.columns;
            let optionContent = '';
            if (this.data[i].html) {
                optionContent = this.data[i].html;
            } else {
                optionContent = `
                    ${this.data[i].img ? `<img src="${this.data[i].img}" alt="${this.data[i].text}" class="${this.data[i].imgWidth && this.data[i].imgHeight ? 'dynamic-size' : ''}" style="${this.data[i].imgWidth ? 'width:' + this.data[i].imgWidth + ';' : ''}${this.data[i].imgHeight ? 'height:' + this.data[i].imgHeight + ';' : ''}">` : ''}
                    ${this.data[i].text ? '<span class="dynamic-select-option-text">' + this.data[i].text + '</span>' : ''}
                    ${this.data[i].type && this.data[i].type !== 'native' ? '<span class="badge bg-warning">' + this.data[i].type + '</span>' : ''}
                `;
            }
            optionsHTML += `
                <div class="dynamic-select-option${this.data[i].value == this.selectedValue ? ' dynamic-select-selected' : ''}${this.data[i].text || this.data[i].html ? '' : ' dynamic-select-no-text'}" data-value="${this.data[i].value}" style="width:${optionWidth}%;${this.height ? 'height:' + this.height + ';' : ''}">
                    ${optionContent}
                </div>
            `;

        }
        let template = `
            <div class="dynamic-select ${this.name}${this.isDisabled ? ' dynamic-select-disabled' : ''}"${this.selectElement.id ? ' id="' + this.selectElement.id + '"' : ''} style="${this.width ? 'width:' + this.width + ';' : ''}${this.height ? 'height:' + this.height + ';' : ''}">
                <input type="hidden" name="${this.name}" value="${this.selectedValue}">
                <div class="dynamic-select-header" style="${this.width ? 'width:' + this.width + ';' : ''}${this.height ? 'height:' + this.height + ';' : ''}"><span class="dynamic-select-header-placeholder">${this.placeholder}</span></div>
                <div class="dynamic-select-options" style="${this.options.dropdownWidth ? 'width:' + this.options.dropdownWidth + ';' : ''}${this.options.dropdownHeight ? 'height:' + this.options.dropdownHeight + ';' : ''}">${optionsHTML}</div>
            </div>
        `;
        let element = document.createElement('div');
        element.innerHTML = template;
        return element;
    }

    _eventHandlers() {
        if (!this.isDisabled) {
            this.element.querySelectorAll('.dynamic-select-option').forEach(option => {
                option.onclick = () => {
                    this.element.querySelectorAll('.dynamic-select-selected').forEach(selected => selected.classList.remove('dynamic-select-selected'));
                    option.classList.add('dynamic-select-selected');
                    this.element.querySelector('.dynamic-select-header').innerHTML = option.innerHTML;
                    this.element.querySelector('input').value = option.getAttribute('data-value');
                    this.data.forEach(data => data.selected = false);
                    this.data.filter(data => data.value == option.getAttribute('data-value'))[0].selected = true;
                    this.element.querySelector('.dynamic-select-header').classList.remove('dynamic-select-header-active');
                    this.options.onChange(option.getAttribute('data-value'), option.querySelector('.dynamic-select-option-text') ? option.querySelector('.dynamic-select-option-text').innerHTML : '', option);
                };
            });
            this.element.querySelector('.dynamic-select-header').onclick = () => {
                this.element.querySelector('.dynamic-select-header').classList.toggle('dynamic-select-header-active');
            };
            if (this.selectElement.id && document.querySelector('label[for="' + this.selectElement.id + '"]')) {
                document.querySelector('label[for="' + this.selectElement.id + '"]').onclick = () => {
                    this.element.querySelector('.dynamic-select-header').classList.toggle('dynamic-select-header-active');
                };
            }
            document.addEventListener('click', event => {
                if (!event.target.closest('.' + this.name) && !event.target.closest('label[for="' + this.selectElement.id + '"]')) {
                    this.element.querySelector('.dynamic-select-header').classList.remove('dynamic-select-header-active');
                }
            });
        }
    }

    _updateSelected() {
        if (this.selectedValue) {
            this.element.querySelector('.dynamic-select-header').innerHTML = this.element.querySelector('.dynamic-select-selected').innerHTML;
        }
    }

    _updateDisabledState() {
        if (this.isDisabled) {
            this.element.classList.add('dynamic-select-disabled');
            this.element.querySelectorAll('.dynamic-select-option').forEach(option => {
                option.onclick = null;
            });
            this.element.querySelector('.dynamic-select-header').onclick = null;
        } else {
            this.element.classList.remove('dynamic-select-disabled');
            this._eventHandlers();
        }
    }

    setDisabled(isDisabled) {
        this.isDisabled = isDisabled;
        this._updateDisabledState();
    }

    setSelectedValue(value) {
        // Find the option with the specified value
        const selectedOption = this.data.find(option => option.value === value);

        if (selectedOption) {
            // Update the selected value in the data array
            this.data.forEach(option => {
                option.selected = (option.value === value);
            });

            // Update the hidden input value
            this.element.querySelector('input').value = value;

            // Update the selected option in the UI
            this.element.querySelectorAll('.dynamic-select-option').forEach(option => {
                if (option.getAttribute('data-value') === value) {
                    option.classList.add('dynamic-select-selected');
                    this.element.querySelector('.dynamic-select-header').innerHTML = option.innerHTML;
                } else {
                    option.classList.remove('dynamic-select-selected');
                }
            });

            // Trigger the onChange callback
            this.options.onChange(value, selectedOption.text, selectedOption);
        } else {
            // Clear the current value and image if the specified value doesn't exist
            this.data.forEach(option => option.selected = false);
            this.element.querySelector('input').value = '';
            this.element.querySelector('.dynamic-select-header').innerHTML = `<span class="dynamic-select-header-placeholder">${this.placeholder}</span>`;
            this.element.querySelectorAll('.dynamic-select-option').forEach(option => {
                option.classList.remove('dynamic-select-selected');
            });

            // Trigger the onChange callback with empty values
            this.options.onChange('', '', null);
        }
    }

    get selectedValue() {
        let selected = this.data.filter(option => option.selected);
        selected = selected.length ? selected[0].value : '';
        return selected;
    }

    set data(value) {
        this.options.data = value;
    }

    get data() {
        return this.options.data;
    }

    set selectElement(value) {
        this.options.selectElement = value;
    }

    get selectElement() {
        return this.options.selectElement;
    }

    set element(value) {
        this.options.element = value;
    }

    get element() {
        return this.options.element;
    }

    set placeholder(value) {
        this.options.placeholder = value;
    }

    get placeholder() {
        return this.options.placeholder;
    }

    set columns(value) {
        this.options.columns = value;
    }

    get columns() {
        return this.options.columns;
    }

    set name(value) {
        this.options.name = value;
    }

    get name() {
        return this.options.name;
    }

    set width(value) {
        this.options.width = value;
    }

    get width() {
        return this.options.width;
    }

    set height(value) {
        this.options.height = value;
    }

    get height() {
        return this.options.height;
    }

}
document.querySelectorAll('[data-dynamic-select]').forEach(select => new DynamicSelect(select));