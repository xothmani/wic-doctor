@extends('layouts.app')

@push('css_lib')
    <!-- select2 -->
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@push('styles')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        .h1 {
            line-height: 2;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2.5em;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .search-section {
            margin-bottom: 30px;
        }

        .search-container {
            position: relative;
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e8ed;
            border-radius: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 10px;
            max-height: 50vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .search-result {
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            transition: background-color 0.2s ease;
        }

        .search-result:hover {
            background-color: #f8f9fa;
        }

        .search-result:last-child {
            border-bottom: none;
        }

        .drug-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .drug-subtext {
            font-size: 0.9em;
            color: #666;
            font-style: italic;
        }

        .selected-drugs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .drug-chip {
            background: #ffffff;
            border: 1px solid #dcdcdc;
            color: transparent;
            background-clip: text;
            -webkit-background-clip: text;
            background-image: linear-gradient(45deg, #667eea, #764ba2);
            padding: 8px 15px;
            border-radius: 25px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
            font-size: 14px;
            animation: slideIn 0.3s ease;
            position: relative;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .drug-chip .drug-name {
            color: transparent;
            background-clip: text;
            -webkit-background-clip: text;
            background-image: linear-gradient(45deg, #667eea, #764ba2);
            font-weight: 600;
        }

        .drug-chip .drug-subtext {
            color: #666;
            font-size: 0.8em;
        }

        .drug-chip .remove {
            position: absolute;
            top: -5px;
            right: -5px;
            cursor: pointer;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: background-color 0.2s ease;
        }

        .drug-chip .remove:hover {
            background: #ff6666;
        }


        .check-button {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .check-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .check-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }



        .results-section {
            margin-top: 30px;
        }

        .interaction {
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .interaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .interaction.no-interactions {
            background: #f0f8ff;
            border: 1px solid #d0e5ff;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
            margin-top: 20px;
            color: #333;
            font-family: 'Segoe UI', sans-serif;
            animation: fadeIn 0.4s ease;
        }

        .interaction.no-interactions .icon {
            font-size: 36px;
            margin-bottom: 10px;
            color: #4caf50;
        }

        .interaction.no-interactions h3 {
            background: linear-gradient(45deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            font-size: 1.5em;
            margin: 0;
        }

        .interaction.no-interactions p {
            color: #555;
            font-size: 0.95em;
            margin-top: 8px;
        }

        .drugs-involved {
            font-weight: 600;
            color: #2c3e50;
            font-size: 18px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .involved-drug,
        .involved-substance {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 4px;
            margin: 2px;
            display: inline-block;
            font-size: 0.9em;
        }

        .severity {
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.8em;
        }

        .severity.low {
            background: #4caf50;
        }

        .severity.moderate {
            background: #ff9800;
        }

        .severity.high {
            background: #ff5722;
        }

        .severity.critical {
            background: #f44336;
        }

        .severity.minor {
            background: #d1ecf1;
            color: #0c5460;
        }

        .severity.major {
            background: #f8d7da;
            color: #721c24;
        }

        .interaction-description {
            color: #495057;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .interaction-management {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            margin-top: 15px;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #667eea;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    </style>
@endpush

@section('content')
    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')

        <div class="container">
            <h1>@lang('lang.ddi-checker')</h1>

            <div class="search-section">
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="@lang('lang.search-drugs-placeholder')"
                        id="drugSearch">
                    <div class="search-results" id="searchResults"></div>
                </div>

                <div class="selected-drugs" id="selectedDrugs"></div>

                <button class="check-button" id="checkButton" disabled>@lang('lang.check')</button>
            </div>

            <div class="results-section" id="resultsSection"></div>
        </div>
    </div>
@endsection

@push('scripts_lib')
    <script type="text/javascript">
        class DrugSearchEngine {
            constructor() {
                this.drugs = [];
                this.selectedDrugs = [];

                this.searchInput = document.getElementById('drugSearch');
                this.searchResults = document.getElementById('searchResults');
                this.selectedContainer = document.getElementById('selectedDrugs');
                this.checkButton = document.getElementById('checkButton');
                this.resultsSection = document.getElementById('resultsSection');

                this.debounceTimer = null;

                this.init();
            }

            async init() {
                await this.loadDrugs();
                this.bindEvents();
            }

            async loadDrugs() {
                try {
                    const response = await fetch('/api/drugs', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    console.log('Loaded drugs:', data); // Debug log

                    this.drugs = data.map(drug => ({
                        id: drug.id,
                        name: drug.display_name || drug.name,
                        subtext: drug.subtext || '',
                        fullName: drug.name,
                        searchableText: (drug.display_name || drug.name).toLowerCase()
                    }));
                } catch (e) {
                    console.error('Error loading drugs:', e);
                    console.warn('Fallback to sample drugs due to load error.');
                    // Fallback data for testing
                    this.drugs = [
                        {
                            id: '60002283',
                            name: 'ANASTROZOLE ACCORD 1 mg',
                            subtext: 'comprimé pelliculé',
                            fullName: 'ANASTROZOLE ACCORD 1 mg, comprimé pelliculé',
                            searchableText: 'anastrozole accord 1 mg'
                        },
                        {
                            id: '60002284',
                            name: 'PARACETAMOL 500 mg',
                            subtext: 'comprimé',
                            fullName: 'PARACETAMOL 500 mg, comprimé',
                            searchableText: 'paracetamol 500 mg'
                        }
                    ];
                }
            }

            bindEvents() {
                // Search input events
                this.searchInput.addEventListener('input', (e) => {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.showResults(e.target.value);
                    }, 300);
                });

                this.searchInput.addEventListener('focus', (e) => {
                    if (e.target.value.length >= 2) {
                        this.showResults(e.target.value);
                    }
                });

                // Click outside to close results
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.search-container')) {
                        this.searchResults.style.display = 'none';
                    }
                });

                // Check button event
                this.checkButton.addEventListener('click', () => this.checkInteractions());
            }

            showResults(query) {
                query = query.toLowerCase().trim();
                console.log('Searching for:', query); // Debug log

                if (query.length < 2) {
                    this.searchResults.style.display = 'none';
                    return;
                }

                const results = this.fuzzySearch(query);
                console.log('Search results:', results); // Debug log

                if (results.length > 0) {
                    this.searchResults.innerHTML = results.map(d => `
                                                        <div class="search-result" data-drug-id="${d.id}">
                                                            <div class="drug-name">${d.name}</div>
                                                            ${d.subtext ? `<div class="drug-subtext">${d.subtext}</div>` : ''}
                                                        </div>
                                                    `).join('');
                } else {
                    this.searchResults.innerHTML = `<div class="search-result">@lang('lang.no-drugs-found')</div>`;
                }

                this.searchResults.style.display = 'block';

                // Add click event listeners to search results
                this.searchResults.querySelectorAll('.search-result[data-drug-id]').forEach(el => {
                    el.addEventListener('click', (e) => {
                        console.log('Clicked drug:', el.dataset.drugId); // Debug log
                        this.addDrug(el.dataset.drugId);
                        this.searchInput.value = '';
                        this.searchResults.style.display = 'none';
                    });
                });
            }

            fuzzySearch(term, limit = 10) {
                return this.drugs
                    .map(drug => {
                        let score = 0;
                        const searchText = drug.searchableText;

                        if (searchText.includes(term)) {
                            score = searchText === term ? 1000 : 800;
                        } else if (this.levenshtein(term, searchText) <= 2 && term.length > 3) {
                            score = 300;
                        }

                        return score > 0 ? { drug, score } : null;
                    })
                    .filter(Boolean)
                    .sort((a, b) => b.score - a.score)
                    .slice(0, limit)
                    .map(r => r.drug);
            }

            levenshtein(a, b) {
                if (a.length === 0) return b.length;
                if (b.length === 0) return a.length;

                const matrix = [];

                for (let i = 0; i <= b.length; i++) {
                    matrix[i] = [i];
                }

                for (let j = 0; j <= a.length; j++) {
                    matrix[0][j] = j;
                }

                for (let i = 1; i <= b.length; i++) {
                    for (let j = 1; j <= a.length; j++) {
                        if (b.charAt(i - 1) === a.charAt(j - 1)) {
                            matrix[i][j] = matrix[i - 1][j - 1];
                        } else {
                            matrix[i][j] = Math.min(
                                matrix[i - 1][j - 1] + 1,
                                matrix[i][j - 1] + 1,
                                matrix[i - 1][j] + 1
                            );
                        }
                    }
                }

                return matrix[b.length][a.length];
            }

            addDrug(id) {
                console.log('Adding drug with ID:', id); // Debug log
                const drug = this.drugs.find(d => d.id == id); // Use == for type coercion

                if (drug && !this.selectedDrugs.some(d => d.id == id)) {
                    this.selectedDrugs.push(drug);
                    console.log('Drug added:', drug); // Debug log
                    console.log('Selected drugs:', this.selectedDrugs); // Debug log
                    this.renderSelectedDrugs();
                    this.updateCheckButton();
                } else {
                    console.log('Drug not found or already selected:', id); // Debug log
                }
            }

            removeDrug(id) {
                console.log('Removing drug with ID:', id); // Debug log
                this.selectedDrugs = this.selectedDrugs.filter(d => d.id != id); // Use != for type coercion
                this.renderSelectedDrugs();
                this.updateCheckButton();
            }

            renderSelectedDrugs() {
                console.log('Rendering selected drugs:', this.selectedDrugs); // Debug log

                this.selectedContainer.innerHTML = this.selectedDrugs.map(d => `
                                                    <div class="drug-chip">
                                                        <span class="drug-name">${d.name}</span>
                                                        ${d.subtext ? `<span class="drug-subtext">${d.subtext}</span>` : ''}
                                                        <div class="remove" data-id="${d.id}">&times;</div>
                                                    </div>
                                                `).join('');

                // Add event listeners to remove buttons
                this.selectedContainer.querySelectorAll('.remove').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.removeDrug(btn.dataset.id);
                    });
                });
            }

            updateCheckButton() {
                this.checkButton.disabled = this.selectedDrugs.length < 2;
                console.log('Check button disabled:', this.checkButton.disabled); // Debug log
            }

            async checkInteractions() {
                const ids = this.selectedDrugs.map(d => d.id);
                console.log('Checking interactions for IDs:', ids); // Debug log

                this.resultsSection.innerHTML = `<div class="loading"><div class="spinner"></div> @lang('lang.checking-interactions')...</div>`;

                try {
                    const response = await fetch('/api/drugs/check-interactions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({
                            drug_ids: ids
                        })
                    });

                    const data = await response.json();
                    console.log('Interaction response:', data); // Debug log

                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to check interactions');
                    }

                    this.renderResults(data);
                } catch (e) {
                    console.error('Error checking interactions:', e); // Debug log
                    this.resultsSection.innerHTML = `<div class="error">@lang('lang.error-interactions'): ${e.message}</div>`;
                }
            }

            renderResults(data) {
                if (!data.interactions || !data.interactions.length) {
                    this.resultsSection.innerHTML = `
                            <div class="interaction no-interactions">
                                <div class="icon">✔️</div>
                                <h3>@lang('lang.no-interactions')</h3>
                                <p>@lang('lang.safe-combination')</p>
                            </div>
                        `;
                    return;
                }

                this.resultsSection.innerHTML = `
                                                    <h3>@lang('lang.interaction-results') (${data.total_results || data.interactions.length})</h3>
                                                    ${data.interactions.map(i => `
                                                        <div class="interaction">
                                                            <div class="interaction-header">
                                                                <div>${i.substances_line || 'Drug Interaction'}</div>
                                                                <div class="severity ${i.severity || 'moderate'}">${this.getSeverityText(i.severity)}</div>
                                                            </div>
                                                            <div class="interaction-description">
                                                                <strong>@lang('lang.effect'):</strong> ${i.effect || 'No effect information available'}
                                                            </div>
                                                            ${i.recommendation ? `<div class="interaction-management">
                                                                <strong>@lang('lang.recommendation'):</strong> ${i.recommendation}
                                                            </div>` : ''}
                                                            ${this.renderLeftBoxes(i.left_boxes)}
                                                        </div>
                                                    `).join('')}
                                                `;
            }

            renderLeftBoxes(leftBoxes) {
                if (!leftBoxes || !leftBoxes.length) return '';

                return `<div class="drugs-involved">
                                                        <strong>@lang('lang.drugs-involved'):</strong>
                                                        ${leftBoxes.map(box => {
                    if (box.drug) {
                        return `<span class="involved-drug">${box.drug.name}${box.drug.dosage ? ` (${box.drug.dosage})` : ''}</span>`;
                    } else if (box.substances && box.substances.length) {
                        return box.substances.map(substance =>
                            `<span class="involved-substance">${substance.name}</span>`
                        ).join(', ');
                    }
                    return '';
                }).filter(Boolean).join(' + ')}
                                                    </div>`;
            }

            getSeverityText(severity) {
                const severityMap = {
                    'low': '@lang("lang.severity-low")',
                    'moderate': '@lang("lang.severity-moderate")',
                    'high': '@lang("lang.severity-high")',
                    'critical': '@lang("lang.severity-critical")',
                    'minor': '@lang("lang.severity-minor")',
                    'major': '@lang("lang.severity-major")'
                };
                return severityMap[severity] || severity;
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, initializing DrugSearchEngine...'); // Debug log
            new DrugSearchEngine();
        });
    </script>
@endpush