<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Design System Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-neutral-50" x-data="{ activeTab: 'tab1' }">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white border-b border-neutral-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-display-md font-bold text-neutral-900">Design System</h1>
                        <p class="text-body-sm text-neutral-600">Komponen UI marketplace yang konsisten dan reusable</p>
                    </div>
                    <a href="/" class="text-body-base text-primary-600 hover:text-primary-700">
                        ← Kembali
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Color Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Palet Warna</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <!-- Primary -->
                        <div>
                            <div class="h-24 rounded-md mb-2" style="background-color: #0ea5e9;"></div>
                            <p class="text-caption font-semibold">Primary</p>
                        </div>
                        <!-- Secondary -->
                        <div>
                            <div class="h-24 rounded-md mb-2" style="background-color: #a855f7;"></div>
                            <p class="text-caption font-semibold">Secondary</p>
                        </div>
                        <!-- Success -->
                        <div>
                            <div class="h-24 rounded-md mb-2" style="background-color: #22c55e;"></div>
                            <p class="text-caption font-semibold">Success</p>
                        </div>
                        <!-- Warning -->
                        <div>
                            <div class="h-24 rounded-md mb-2" style="background-color: #f59e0b;"></div>
                            <p class="text-caption font-semibold">Warning</p>
                        </div>
                        <!-- Danger -->
                        <div>
                            <div class="h-24 rounded-md mb-2" style="background-color: #ef4444;"></div>
                            <p class="text-caption font-semibold">Danger</p>
                        </div>
                        <!-- Neutral -->
                        <div>
                            <div class="h-24 rounded-md mb-2" style="background-color: #6b7280;"></div>
                            <p class="text-caption font-semibold">Neutral</p>
                        </div>
                    </div>
                </x-card>

                <!-- Buttons Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Buttons</h2>
                    
                    <!-- Variants -->
                    <div class="mb-8">
                        <h3 class="text-heading-sm mb-4">Variants</h3>
                        <div class="flex flex-wrap gap-3">
                            <x-button variant="primary">Primary</x-button>
                            <x-button variant="secondary">Secondary</x-button>
                            <x-button variant="success">Success</x-button>
                            <x-button variant="warning">Warning</x-button>
                            <x-button variant="danger">Danger</x-button>
                            <x-button variant="ghost">Ghost</x-button>
                            <x-button variant="outline">Outline</x-button>
                        </div>
                    </div>

                    <!-- Sizes -->
                    <div class="mb-8">
                        <h3 class="text-heading-sm mb-4">Sizes</h3>
                        <div class="flex flex-wrap gap-3 items-center">
                            <x-button size="sm">Small</x-button>
                            <x-button size="md">Medium</x-button>
                            <x-button size="lg">Large</x-button>
                            <x-button size="xl">Extra Large</x-button>
                        </div>
                    </div>

                    <!-- States -->
                    <div>
                        <h3 class="text-heading-sm mb-4">States</h3>
                        <div class="flex flex-wrap gap-3">
                            <x-button variant="primary">Normal</x-button>
                            <x-button variant="primary" disabled>Disabled</x-button>
                        </div>
                    </div>
                </x-card>

                <!-- Form Components Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Form Components</h2>
                    
                    <div class="space-y-6">
                        <!-- Input -->
                        <div>
                            <x-form-input name="demo-input" label="Text Input" placeholder="Masukkan teks..." />
                        </div>

                        <!-- Select -->
                        <div>
                            <x-select name="demo-select" label="Select">
                                <option value="">-- Pilih opsi --</option>
                                <option value="1">Opsi 1</option>
                                <option value="2">Opsi 2</option>
                                <option value="3">Opsi 3</option>
                            </x-select>
                        </div>

                        <!-- Checkboxes -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Checkboxes</label>
                            <div class="space-y-2">
                                <x-checkbox name="check1" value="1" label="Checkbox 1" />
                                <x-checkbox name="check2" value="2" label="Checkbox 2" />
                            </div>
                        </div>

                        <!-- Radios -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Radio Buttons</label>
                            <div class="space-y-2">
                                <x-radio name="radio" value="1" label="Option 1" />
                                <x-radio name="radio" value="2" label="Option 2" />
                            </div>
                        </div>

                        <!-- Textarea -->
                        <div>
                            <x-textarea name="demo-textarea" label="Textarea" rows="4" placeholder="Masukkan deskripsi..." />
                        </div>
                    </div>
                </x-card>

                <!-- Badges Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Badges</h2>
                    
                    <!-- Variants -->
                    <div class="mb-6">
                        <h3 class="text-heading-sm mb-3">Variants</h3>
                        <div class="flex flex-wrap gap-2">
                            <x-badge variant="primary">Primary</x-badge>
                            <x-badge variant="secondary">Secondary</x-badge>
                            <x-badge variant="success">Success</x-badge>
                            <x-badge variant="warning">Warning</x-badge>
                            <x-badge variant="danger">Danger</x-badge>
                            <x-badge variant="neutral">Neutral</x-badge>
                        </div>
                    </div>

                    <!-- Sizes -->
                    <div>
                        <h3 class="text-heading-sm mb-3">Sizes</h3>
                        <div class="flex flex-wrap gap-2">
                            <x-badge size="sm">Small</x-badge>
                            <x-badge size="md">Medium</x-badge>
                            <x-badge size="lg">Large</x-badge>
                        </div>
                    </div>
                </x-card>

                <!-- Alerts Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Alerts</h2>
                    
                    <div class="space-y-4 mb-6">
                        <x-alert type="info" title="Info Alert">
                            Ini adalah pesan informasi untuk pengguna.
                        </x-alert>

                        <x-alert type="success" title="Success Alert">
                            Data telah berhasil disimpan ke sistem.
                        </x-alert>

                        <x-alert type="warning" title="Warning Alert">
                            Perhatian: Tindakan ini tidak dapat dibatalkan.
                        </x-alert>

                        <x-alert type="danger" title="Error Alert">
                            Terjadi kesalahan saat memproses permintaan Anda.
                        </x-alert>
                    </div>

                    <!-- Interactive Toast Alerts -->
                    <div x-data="{
                        showInfo: false,
                        showSuccess: false,
                        showWarning: false,
                        showDanger: false,
                        triggerToast(type) {
                            this[`show${type.charAt(0).toUpperCase() + type.slice(1)}`] = true;
                            setTimeout(() => {
                                this[`show${type.charAt(0).toUpperCase() + type.slice(1)}`] = false;
                            }, 4000);
                        }
                    }">
                        <h3 class="text-heading-sm mb-4">Trigger Toast Alerts</h3>
                        <div class="flex flex-wrap gap-3">
                            <x-button @click="triggerToast('info')" variant="primary">Show Info</x-button>
                            <x-button @click="triggerToast('success')" variant="success">Show Success</x-button>
                            <x-button @click="triggerToast('warning')" variant="warning">Show Warning</x-button>
                            <x-button @click="triggerToast('danger')" variant="danger">Show Error</x-button>
                        </div>

                        <!-- Toast Container -->
                        <div class="fixed top-4 right-4 z-50 space-y-3">
                            <div x-show="showInfo" x-transition class="min-w-80">
                                <div style="background-color: #f0f7ff; border-color: #bae3ff; color: #0c3d66;" class="border rounded-md p-4 bg-white shadow-lg">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 text-blue-600">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold mb-1">Info</h3>
                                            <p class="text-sm">Ini adalah pesan informasi untuk Anda.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="showSuccess" x-transition class="min-w-80">
                                <div style="background-color: #f0fdf4; border-color: #bbf7d0; color: #15803d;" class="border rounded-md p-4 bg-white shadow-lg">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 text-green-600">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold mb-1">Success</h3>
                                            <p class="text-sm">Operasi berhasil dilakukan!</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="showWarning" x-transition class="min-w-80">
                                <div style="background-color: #fffbeb; border-color: #fde68a; color: #b45309;" class="border rounded-md p-4 bg-white shadow-lg">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 text-yellow-600">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold mb-1">Warning</h3>
                                            <p class="text-sm">Perhatikan peringatan ini dengan seksama.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="showDanger" x-transition class="min-w-80">
                                <div style="background-color: #fef2f2; border-color: #fecaca; color: #b91c1c;" class="border rounded-md p-4 bg-white shadow-lg">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 text-red-600">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold mb-1">Error</h3>
                                            <p class="text-sm">Terjadi kesalahan yang perlu segera ditangani.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>

                <!-- Card Variations -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Cards</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-card shadow="sm">
                            <h3 class="text-heading-sm mb-2">Card Shadow SM</h3>
                            <p class="text-body-sm text-neutral-600">Kartu dengan shadow kecil</p>
                        </x-card>

                        <x-card shadow="md">
                            <h3 class="text-heading-sm mb-2">Card Shadow MD</h3>
                            <p class="text-body-sm text-neutral-600">Kartu dengan shadow medium</p>
                        </x-card>

                        <x-card shadow="lg">
                            <h3 class="text-heading-sm mb-2">Card Shadow LG</h3>
                            <p class="text-body-sm text-neutral-600">Kartu dengan shadow besar</p>
                        </x-card>
                    </div>
                </x-card>

                <!-- Typography -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Typography</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-display-lg font-bold">Display Large</h3>
                            <p class="text-caption">2.5rem, font-bold</p>
                        </div>

                        <div>
                            <h3 class="text-heading-lg">Heading Large</h3>
                            <p class="text-caption">1.875rem, font-bold</p>
                        </div>

                        <div>
                            <h3 class="text-heading-md">Heading Medium</h3>
                            <p class="text-caption">1.5rem, font-bold</p>
                        </div>

                        <div>
                            <h3 class="text-heading-sm">Heading Small</h3>
                            <p class="text-caption">1.25rem, font-semibold</p>
                        </div>

                        <div>
                            <p class="text-body-lg">Body Large - 1.125rem</p>
                        </div>

                        <div>
                            <p class="text-body-base">Body Base - 1rem (default)</p>
                        </div>

                        <div>
                            <p class="text-body-sm">Body Small - 0.875rem</p>
                        </div>

                        <div>
                            <p class="text-caption">Caption - 0.75rem, neutral-600</p>
                        </div>
                    </div>
                </x-card>

                <!-- Modal Demo -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Modal</h2>
                    
                    <x-button @click="$dispatch('open-modal', { name: 'demo-modal' })">
                        Open Modal
                    </x-button>

                    <x-modal name="demo-modal">
                        <div class="p-6">
                            <h2 class="text-heading-md mb-4">Modal Demo</h2>
                            <p class="text-body-base text-neutral-600 mb-6">
                                Ini adalah contoh modal dengan design system yang konsisten.
                            </p>
                            <div class="flex gap-3 justify-end">
                                <x-button variant="ghost" @click="$dispatch('close')">
                                    Close
                                </x-button>
                                <x-button variant="primary">
                                    Confirm
                                </x-button>
                            </div>
                        </div>
                    </x-modal>
                </x-card>

                <!-- Spinner Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Spinners</h2>
                    
                    <div class="flex flex-wrap gap-6 items-center">
                        <div class="text-center">
                            <x-spinner size="sm" class="mx-auto mb-2" />
                            <p class="text-caption">Small</p>
                        </div>
                        <div class="text-center">
                            <x-spinner size="md" class="mx-auto mb-2" />
                            <p class="text-caption">Medium</p>
                        </div>
                        <div class="text-center">
                            <x-spinner size="lg" class="mx-auto mb-2" />
                            <p class="text-caption">Large</p>
                        </div>
                        <div class="text-center">
                            <x-spinner variant="primary" size="md" class="mx-auto mb-2" />
                            <p class="text-caption">Primary</p>
                        </div>
                        <div class="text-center">
                            <x-spinner variant="success" size="md" class="mx-auto mb-2" />
                            <p class="text-caption">Success</p>
                        </div>
                    </div>
                </x-card>

                <!-- Toggle/Switch Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Toggles & Switches</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <x-toggle name="toggle1" value="1" />
                            <label class="text-body-base font-medium">Enable notifications</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-toggle name="toggle2" value="1" disabled />
                            <label class="text-body-base font-medium text-neutral-500">Disabled toggle</label>
                        </div>
                    </div>
                </x-card>

                <!-- Slider Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Sliders</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="text-body-sm font-semibold mb-3 block">Price Range (0-1000)</label>
                            <x-slider name="price" min="0" max="1000" value="500" step="10" />
                        </div>
                        <div>
                            <label class="text-body-sm font-semibold mb-3 block">Volume (0-100)</label>
                            <x-slider name="volume" min="0" max="100" value="50" step="5" />
                        </div>
                    </div>
                </x-card>

                <!-- Divider Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Dividers</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <p class="text-body-sm text-neutral-600 mb-3">Horizontal Divider</p>
                            <x-divider />
                        </div>
                        <div>
                            <p class="text-body-sm text-neutral-600 mb-3">Horizontal with margin</p>
                            <x-divider class="my-4" />
                        </div>
                    </div>
                </x-card>

                <!-- Dropdown Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Dropdowns</h2>
                    
                    <x-dropdown label="Dropdown Menu">
                        <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Profile</a>
                        <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Settings</a>
                        <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Help</a>
                        <hr class="my-1" />
                        <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
                    </x-dropdown>
                </x-card>

                <!-- Tabs Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Tabs</h2>
                    
                    <x-tabs>
                        <x-slot:tabs>
                            <button @click="activeTab = 'tab1'" :class="activeTab === 'tab1' ? 'border-b-2 border-sky-500 text-sky-600' : 'text-neutral-600'" class="px-4 py-2 font-semibold">Tab 1</button>
                            <button @click="activeTab = 'tab2'" :class="activeTab === 'tab2' ? 'border-b-2 border-sky-500 text-sky-600' : 'text-neutral-600'" class="px-4 py-2 font-semibold">Tab 2</button>
                            <button @click="activeTab = 'tab3'" :class="activeTab === 'tab3' ? 'border-b-2 border-sky-500 text-sky-600' : 'text-neutral-600'" class="px-4 py-2 font-semibold">Tab 3</button>
                        </x-slot:tabs>
                        
                        <x-slot:content>
                            <div x-show="activeTab === 'tab1'" class="py-4">
                                <p class="text-body-base">Konten Tab 1 - Ini adalah contoh konten dalam tab pertama.</p>
                            </div>
                            <div x-show="activeTab === 'tab2'" class="py-4">
                                <p class="text-body-base">Konten Tab 2 - Ini adalah contoh konten dalam tab kedua.</p>
                            </div>
                            <div x-show="activeTab === 'tab3'" class="py-4">
                                <p class="text-body-base">Konten Tab 3 - Ini adalah contoh konten dalam tab ketiga.</p>
                            </div>
                        </x-slot:content>
                    </x-tabs>
                </x-card>

                <!-- Accordion Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Accordions</h2>
                    
                    <div class="space-y-2">
                        <x-accordion title="Section 1">
                            <p class="text-body-sm text-neutral-600">Ini adalah konten accordion bagian pertama dengan penjelasan detail tentang fitur atau informasi penting.</p>
                        </x-accordion>
                        
                        <x-accordion title="Section 2">
                            <p class="text-body-sm text-neutral-600">Ini adalah konten accordion bagian kedua dengan informasi tambahan yang dapat diekspansi sesuai kebutuhan.</p>
                        </x-accordion>
                        
                        <x-accordion title="Section 3">
                            <p class="text-body-sm text-neutral-600">Ini adalah konten accordion bagian ketiga untuk menampilkan lebih banyak informasi tanpa perlu scroll.</p>
                        </x-accordion>
                    </div>
                </x-card>

                <!-- Offcanvas Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Offcanvas / Side Drawer</h2>
                    
                    <x-button @click="$dispatch('open-modal', { name: 'demo-offcanvas' })">
                        Open Offcanvas
                    </x-button>

                    <x-offcanvas name="demo-offcanvas" title="Side Menu">
                        <div class="space-y-4">
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded">Dashboard</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded">Products</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded">Orders</a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded">Settings</a>
                        </div>
                    </x-offcanvas>
                </x-card>

                <!-- Tooltip Section -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Tooltips</h2>
                    
                    <div class="flex flex-wrap gap-6">
                        <x-tooltip text="This is a tooltip at the bottom">
                            <x-button>Hover me</x-button>
                        </x-tooltip>
                        
                        <x-tooltip text="Tooltip at top" position="top">
                            <x-button variant="secondary">Top Tooltip</x-button>
                        </x-tooltip>
                        
                        <x-tooltip text="Tooltip at left" position="left">
                            <x-button variant="success">Left Tooltip</x-button>
                        </x-tooltip>
                        
                        <x-tooltip text="Tooltip at right" position="right">
                            <x-button variant="warning">Right Tooltip</x-button>
                        </x-tooltip>
                    </div>
                </x-card>

                <!-- File Upload -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">File Upload</h2>
                    <x-file-upload name="demo_file" accept="image/*" />
                </x-card>

                <!-- Spacing System -->
                <x-card shadow="lg" padding="lg" class="mb-8">
                    <h2 class="text-heading-lg mb-6">Spacing System</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <p class="text-caption mb-3">XS (0.5rem / 8px)</p>
                            <div style="width: 8px; height: 32px; background-color: #dbeafe;"></div>
                        </div>
                        <div>
                            <p class="text-caption mb-3">SM (0.75rem / 12px)</p>
                            <div style="width: 12px; height: 32px; background-color: #bfdbfe;"></div>
                        </div>
                        <div>
                            <p class="text-caption mb-3">MD (1rem / 16px)</p>
                            <div style="width: 16px; height: 32px; background-color: #7ed4ff;"></div>
                        </div>
                        <div>
                            <p class="text-caption mb-3">LG (1.5rem / 24px)</p>
                            <div style="width: 24px; height: 32px; background-color: #38bdf8;"></div>
                        </div>
                        <div>
                            <p class="text-caption mb-3">XL (2rem / 32px)</p>
                            <div style="width: 32px; height: 32px; background-color: #0ea5e9;"></div>
                        </div>
                    </div>
                </x-card>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-neutral-900 text-neutral-400 py-8 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-body-sm">
                <p>Design System Demo - All components are responsive and accessible</p>
            </div>
        </footer>
    </div>
</body>
</html>
