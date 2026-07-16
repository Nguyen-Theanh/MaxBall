<script>
    (() => {
        const endpoints = {
            provinces: @json(route('api.vietnam-address.provinces')),
            wards: @json(route('api.vietnam-address.wards')),
        };

        const selectors = new Map();

        class VietnamAddressSelector {
            constructor(root) {
                this.root = root;
                this.prefix = root.dataset.addressPrefix;
                this.provinceSelect = root.querySelector('[data-province-select]');
                this.wardSelect = root.querySelector('[data-ward-select]');
                this.status = root.querySelector('[data-address-status]');
                this.statusText = root.querySelector('[data-address-status-text]');
                this.retryButton = root.querySelector('[data-address-retry]');
                this.wardRequestId = 0;

                this.provinceSelect.addEventListener('change', () => {
                    this.root.dataset.initialProvince = this.provinceSelect.value;
                    this.root.dataset.initialWard = '';

                    if (this.provinceSelect.value) {
                        this.loadWards(this.provinceSelect.value);
                    } else {
                        this.fillSelect(this.wardSelect, [], 'Chọn tỉnh/thành trước');
                        this.wardSelect.disabled = true;
                        this.hideError();
                    }
                });

                this.retryButton.addEventListener('click', () => {
                    if (this.provinceSelect.options.length > 1 && this.provinceSelect.value) {
                        this.loadWards(this.provinceSelect.value, this.root.dataset.initialWard);
                        return;
                    }

                    this.ready = this.loadProvinces();
                });

                this.ready = this.loadProvinces();
            }

            async fetchData(url) {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                });
                const payload = await response.json().catch(() => ({}));

                if (! response.ok) {
                    throw new Error(payload.message || 'Không thể tải dữ liệu địa chỉ.');
                }

                return Array.isArray(payload.data) ? payload.data : [];
            }

            fillSelect(select, units, placeholder) {
                select.replaceChildren();

                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.textContent = placeholder;
                select.appendChild(placeholderOption);

                units.forEach((unit) => {
                    const option = document.createElement('option');
                    option.value = String(unit.code);
                    option.textContent = unit.name;
                    select.appendChild(option);
                });
            }

            async loadProvinces() {
                this.provinceSelect.disabled = true;
                this.wardSelect.disabled = true;
                this.fillSelect(this.provinceSelect, [], 'Đang tải tỉnh/thành...');
                this.fillSelect(this.wardSelect, [], 'Chọn tỉnh/thành trước');
                this.hideError();

                try {
                    const provinces = await this.fetchData(endpoints.provinces);
                    this.fillSelect(this.provinceSelect, provinces, 'Chọn tỉnh/thành phố');
                    this.provinceSelect.disabled = false;

                    const initialProvince = this.root.dataset.initialProvince;
                    const initialWard = this.root.dataset.initialWard;

                    if (initialProvince && this.hasOption(this.provinceSelect, initialProvince)) {
                        this.provinceSelect.value = String(initialProvince);
                        await this.loadWards(initialProvince, initialWard);
                    }
                } catch (error) {
                    this.fillSelect(this.provinceSelect, [], 'Không tải được tỉnh/thành');
                    this.showError(error.message);
                }
            }

            async loadWards(provinceCode, selectedWard = '') {
                const requestId = ++this.wardRequestId;
                this.wardSelect.disabled = true;
                this.fillSelect(this.wardSelect, [], 'Đang tải xã/phường...');
                this.hideError();

                try {
                    const url = new URL(endpoints.wards, window.location.origin);
                    url.searchParams.set('province_code', provinceCode);
                    const wards = await this.fetchData(url);

                    if (requestId !== this.wardRequestId) {
                        return;
                    }

                    this.fillSelect(this.wardSelect, wards, 'Chọn xã/phường');
                    this.wardSelect.disabled = false;

                    if (selectedWard && this.hasOption(this.wardSelect, selectedWard)) {
                        this.wardSelect.value = String(selectedWard);
                    }
                } catch (error) {
                    if (requestId !== this.wardRequestId) {
                        return;
                    }

                    this.fillSelect(this.wardSelect, [], 'Không tải được xã/phường');
                    this.showError(error.message);
                }
            }

            async setSelection(provinceCode = '', wardCode = '') {
                this.root.dataset.initialProvince = provinceCode || '';
                this.root.dataset.initialWard = wardCode || '';
                await this.ready;

                if (! provinceCode || ! this.hasOption(this.provinceSelect, provinceCode)) {
                    this.reset();
                    return;
                }

                this.provinceSelect.value = String(provinceCode);
                await this.loadWards(provinceCode, wardCode);
            }

            reset() {
                this.root.dataset.initialProvince = '';
                this.root.dataset.initialWard = '';
                this.wardRequestId++;
                this.provinceSelect.value = '';
                this.fillSelect(this.wardSelect, [], 'Chọn tỉnh/thành trước');
                this.wardSelect.disabled = true;
                this.hideError();
            }

            hasOption(select, value) {
                return Array.from(select.options).some((option) => option.value === String(value));
            }

            showError(message) {
                this.statusText.textContent = message;
                this.status.classList.remove('hidden');
                this.status.classList.add('flex');
            }

            hideError() {
                this.status.classList.add('hidden');
                this.status.classList.remove('flex');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-vietnam-address]').forEach((root) => {
                const selector = new VietnamAddressSelector(root);
                selectors.set(selector.prefix, selector);
            });
        });

        window.VietnamAddress = {
            get(prefix) {
                return selectors.get(prefix);
            },
        };
    })();
</script>
