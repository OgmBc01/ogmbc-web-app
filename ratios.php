<?php
include 'includes/database.php';
include 'includes/header-1.php'
?> 

<style>
    :root {
        --gold: #f1bf70;
        --primary: #0b1224;
        --muted: #94a3b8;
    }

    .card-app { background: rgba(255,255,255,0.04); border:1px solid rgba(241,191,112,0.06); }
    .stepper { counter-reset: step; }
    .step { position: relative; padding-left:3rem; margin-bottom:1.25rem; }
    .step::before { counter-increment: step; content: counter(step); width:2rem; height:2rem; border-radius:50%; background:var(--gold); color:var(--primary); display:inline-grid; place-items:center; position:absolute; left:0; top:0; font-weight:700; }
    
    /* Ratio card styling */
    .ratio-card {
        background: #0b1224;
        border-radius: 10px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #e2e8f0;
        border: 2px solid transparent;
    }
    .ratio-card:hover {
        transform: translateY(-4px);
        border-color: var(--gold);
        box-shadow: 0 0 8px rgba(241,191,112,0.4);
    }
    .ratio-card.selected {
        border-color: var(--gold);
        box-shadow: 0 0 10px rgba(241,191,112,0.7);
    }

    /* Abbreviation square patch */
    .ratio-abbr {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        background: #f1c070ff;
        display: grid;
        place-items: center;
        color: #fff;
        font-weight: 700;
        font-size: 1.1rem;
    }

    /* Ratio title golden */
    .ratio-name {
        color: var(--gold);
        font-weight: 600;
        font-size: 1.05rem;
    }

    .badge-good { background: #16a34a; color: #fff; }
    .badge-warn { background: #f59e0b; color: #fff; }
    .badge-risk { background: #dc2626; color: #fff; }
    .small-muted { color: var(--muted); font-size:.95rem; }

    .modal { z-index: 99999; }
    .modal-backdrop.show { z-index: 99998; }

    .saved-table td, .saved-table th { color: #0f172a; }
    .bg-paper { background: #f7f6f2; color: #0f172a; }
</style>

<!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
    <div class="container">
        <h1 class="display-4 fw-bold">Free Ratio Calculator</h1>
        <p class="lead">Easily find out the status or faith of your business with our comprehensive & easy to use ratio calculator.</p>
        <nav aria-label="breadcrumb">
            <!-- <ol class="breadcrumb justify-content-center">
                <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none"></a></li>
                <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none"></a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"></li>
            </ol> -->
        </nav>
    </div>
</section>

<!-- Ratios Calculator Section -->
<section class="ratios-calculator py-5">
    <div class="container">
        <div class="card card-app shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Get Started</h3>
                </div>

                <!-- Stepper -->
                <div id="stepper" class="stepper mb-4">
                    <!-- STEP 1 -->
                    <div class="step" data-step="1">
                        <h5 class="mb-1">Step 1 — Select Ratio(s)</h5>
                        <p class="small-muted mb-2">Choose one or more ratios to calculate.</p>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="row g-2">
                                    <div id="ratiosGrid" class="d-flex flex-wrap gap-2"></div>
                                </div>
                            </div>
                            <div class="col-12 text-end mt-2">
                                <button id="toStep2" class="btn btn-primary" disabled>Next: Enter Inputs</button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2 -->
                    <div class="step" data-step="2" style="display:none;">
                        <h5 class="mb-1">Step 2 — Enter Inputs</h5>
                        <p class="small-muted mb-2">You will be calculating the ratios for the below listed categories.</br>Only fields required for the chosen ratios are shown.</p>

                        <!-- Display selected ratio names -->
                        <div id="selectedRatiosHeader" class="p-3 mb-3 rounded-3" style="background:#0b1224; color:var(--gold); font-weight:600; display:none;">
                        </div>

                        <form id="inputsForm" novalidate>
                            <div id="inputsContainer" class="row g-3"></div>

                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <button id="backToStep1" type="button" class="btn btn-secondary">← Back</button>
                                <div>
                                    <button id="calcBtn" type="button" class="btn btn-gold btn-lg" style="background:var(--gold); color:var(--primary);">Calculate</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <hr class="border-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Saved Results</h6>
                    <div><button id="clearSaved" class="btn btn-sm btn-outline-light">Clear Saved</button></div>
                </div>
                <div id="savedArea" class="mb-3"><div class="small-muted">No saved results yet.</div></div>
            </div>
        </div>

        <!-- Results Modal -->
        <div class="modal fade" id="resultsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-paper">
                        <div>
                            <h5 class="modal-title">Calculation Results</h5>
                            <div class="small-muted">OGMBC Consultants — Financial Ratios Report</div>
                        </div>
                        <div class="ms-auto">
                            <button id="saveResultBtn" class="btn btn-sm btn-outline-primary me-2">Save</button>
                            <button id="downloadPdfBtn" class="btn btn-sm btn-outline-success me-2">Download PDF</button>
                            <a id="contactOgm" class="btn btn-sm btn-dark" href="mailto:info@ogmconsultants.com?subject=Help%20with%20financial%20ratios" target="_blank">Contact OGM</a>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="modal-body bg-paper">
                        <div id="resultsContainer"></div>
                    </div>
                    <div class="modal-footer">
                        <small class="text-muted me-auto">Tip: Save results to compare periods or download the PDF for your records. <a href="contact.php" style="color: var(--gold)">Contact OGMBC</a> for more useful tips or assistance.</small>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete confirm modal -->
        <div class="modal fade" id="confirmDeleteSaved" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <p>Delete this saved result?</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <button id="confirmDeleteYes" class="btn btn-danger">Yes</button>
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Floating Buttons -->
<div class="floating-buttons">
    <a href="https://wa.me/+971502923136" class="floating-btn whatsapp-btn" target="_blank"><i class="bi bi-whatsapp"></i></a>
    <a href="#" class="floating-btn back-to-top"><i class="bi bi-arrow-up"></i></a>
</div>

<?php include 'includes/footer.php' ?>


<script>
  /* =========================
    Ratio Definitions + UI data
    ========================= */
  /* ======= REPLACE THE RATIOS BLOCK WITH THIS CLEAN VERSION ======= */
  const RATIOS = {
    current_ratio: {
      label: "Current Ratio",
      inputs: [
        { k: "current_assets", label: "Current Assets" },
        { k: "current_liabilities", label: "Current Liabilities" }
      ],
      formula: (v) => (v.current_liabilities === 0 ? null : v.current_assets / v.current_liabilities),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 1.5 ? ["good"] : val >= 1.0 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data — cannot divide by zero."];
        if (val > 1.5) return ["No immediate action; maintain working capital discipline."];
        if (val >= 1.0) return ["Improve receivables collection, negotiate payables, manage inventory."];
        return ["Urgent: obtain short-term funding; accelerate collections; reduce discretionary spend."];
      }
    },

    quick_ratio: {
      label: "Quick Ratio (Acid Test)",
      inputs: [
        { k: "current_assets", label: "Current Assets" },
        { k: "inventory", label: "Inventory" },
        { k: "current_liabilities", label: "Current Liabilities" }
      ],
      formula: (v) => (v.current_liabilities === 0 ? null : (v.current_assets - v.inventory) / v.current_liabilities),
      thresholds: (val) => (val === null ? ["insufficient"] : val >= 1.0 ? ["good"] : val >= 0.7 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data — cannot divide by zero."];
        if (val >= 1.0) return ["Maintain liquidity; optimize working capital."];
        if (val >= 0.7) return ["Consider short-term credit facilities; improve collections."];
        return ["Urgent: restructure short-term liabilities; seek funding."];
      }
    },

    gross_margin: {
      label: "Gross Profit Margin",
      inputs: [
        { k: "revenue", label: "Revenue" },
        { k: "cogs", label: "Cost of Goods Sold (COGS)" }
      ],
      formula: (v) => (v.revenue === 0 ? null : (v.revenue - v.cogs) / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.40 ? ["good"] : val >= 0.20 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val > 0.40) return ["Keep pricing discipline; manage costs to protect margins."];
        if (val >= 0.20) return ["Review pricing, supplier costs; improve product mix."];
        return ["Consider price increases, cost-cutting, or product rationalization."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%")
    },

    net_margin: {
      label: "Net Profit Margin",
      inputs: [
        { k: "net_income", label: "Net Income" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : v.net_income / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.12 ? ["good"] : val >= 0.05 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val > 0.12) return ["Healthy profitability — keep focusing on growth and efficiency."];
        if (val >= 0.05) return ["Improve operating efficiency and overhead controls."];
        return ["Immediate focus: cut costs, improve pricing, evaluate operational changes."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%")
    },

    roa: {
      label: "Return on Assets (ROA)",
      inputs: [
        { k: "net_income", label: "Net Income" },
        { k: "total_assets", label: "Total Assets" }
      ],
      formula: (v) => (v.total_assets === 0 ? null : v.net_income / v.total_assets),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.08 ? ["good"] : val >= 0.03 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Total Assets zero)."];
        if (val > 0.08) return ["Good asset utilization; consider reinvestment."];
        if (val >= 0.03) return ["Improve asset efficiency; focus on high-return activities."];
        return ["Review asset base; divest underperforming assets."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%")
    },

    roe: {
      label: "Return on Equity (ROE)",
      inputs: [
        { k: "net_income", label: "Net Income" },
        { k: "shareholders_equity", label: "Shareholders' Equity" }
      ],
      formula: (v) => (v.shareholders_equity === 0 ? null : v.net_income / v.shareholders_equity),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.12 ? ["good"] : val >= 0.06 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Equity zero)."];
        if (val > 0.12) return ["Strong returns for equity holders."];
        if (val >= 0.06) return ["Moderate returns; optimize leverage and profitability."];
        return ["Low returns; review capital allocation strategy."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%")
    },

    inventory_turnover: {
      label: "Inventory Turnover",
      inputs: [
        { k: "cogs", label: "Cost of Goods Sold (COGS)" },
        { k: "avg_inventory", label: "Average Inventory" }
      ],
      formula: (v) => (v.avg_inventory === 0 ? null : v.cogs / v.avg_inventory),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 6 ? ["good"] : val >= 3 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Average inventory zero)."];
        if (val > 6) return ["Strong turnover; keep replenishment tight."];
        if (val >= 3) return ["Optimize inventory; remove slow movers."];
        return ["Too slow — discounting, improve forecasting, JIT strategies."];
      }
    },

    dso: {
      label: "Days Sales Outstanding (DSO)",
      inputs: [
        { k: "accounts_receivable", label: "Accounts Receivable" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : (v.accounts_receivable / v.revenue) * 365),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 45 ? ["good"] : val <= 90 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val < 45) return ["Collections are good; keep policies tight."];
        if (val <= 90) return ["Improve AR collections; tighten credit policy."];
        return ["Urgent: enforce collections, review credit terms."];
      },
      formatVal: (v) => (v === null ? "N/A" : Math.round(v) + " days")
    },

    asset_turnover: {
      label: "Asset Turnover",
      inputs: [
        { k: "revenue", label: "Revenue" },
        { k: "total_assets", label: "Total Assets" }
      ],
      formula: (v) => (v.total_assets === 0 ? null : v.revenue / v.total_assets),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 1 ? ["good"] : val >= 0.5 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Total assets zero)."];
        if (val > 1) return ["High efficiency — scale operations."];
        if (val >= 0.5) return ["Improve asset use & sales productivity."];
        return ["Low utilization — review asset base."];
      }
    },

    debt_to_equity: {
      label: "Debt to Equity",
      inputs: [
        { k: "total_liabilities", label: "Total Liabilities" },
        { k: "shareholders_equity", label: "Shareholders' Equity" }
      ],
      formula: (v) => (v.shareholders_equity === 0 ? null : v.total_liabilities / v.shareholders_equity),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 1 ? ["good"] : val <= 2 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Equity zero)."];
        if (val < 1) return ["Conservative leverage."];
        if (val <= 2) return ["Monitor leverage and interest costs."];
        return ["High leverage — consider deleveraging or refinancing."];
      }
    },

    interest_coverage: {
      label: "Interest Coverage Ratio (EBIT / Interest Expense)",
      inputs: [
        { k: "ebit", label: "EBIT" },
        { k: "interest_expense", label: "Interest Expense" }
      ],
      formula: (v) => (v.interest_expense === 0 ? null : v.ebit / v.interest_expense),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 3 ? ["good"] : val >= 1.5 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Interest expense zero)."];
        if (val > 3) return ["Comfortable interest coverage."];
        if (val >= 1.5) return ["Tight coverage — consider reducing interest or boosting EBIT."];
        return ["Poor coverage — urgent refinancing or cost reduction needed."];
      }
    },

    operating_cash_flow_ratio: {
      label: "Operating Cash Flow Ratio",
      inputs: [
        { k: "operating_cash_flow", label: "Operating Cash Flow" },
        { k: "current_liabilities", label: "Current Liabilities" }
      ],
      formula: (v) => (v.current_liabilities === 0 ? null : v.operating_cash_flow / v.current_liabilities),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.2 ? ["good"] : val >= 0.1 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Current liabilities zero)."];
        if (val > 0.2) return ["Strong cash flow vs short-term obligations."];
        if (val >= 0.1) return ["Improve operational cash collection."];
        return ["Cash flow risk — focus on working capital relief."];
      }
    }
  };

  /* Helper utilities */
  const formatNumber = (n) => {
    if (n === null) return 'N/A';
    if (typeof n === 'number' && Math.abs(n) >= 1) return n.toLocaleString(undefined, {maximumFractionDigits:2});
    return (Math.round(n*100)/100).toString();
  };
  const severityBadge = status => {
    if (status==='good') return '<span class="badge badge-good me-1">Good</span>';
    if (status==='warn') return '<span class="badge badge-warn me-1">Warning</span>';
    if (status==='risk') return '<span class="badge badge-risk me-1">Risk</span>';
    return '<span class="badge bg-secondary me-1">N/A</span>';
  };

  /* ===== RENDER RATIO OPTIONS (updated for golden name + square patch) ===== */
  function renderRatioOptions(){
    const container = document.getElementById('ratiosGrid');
    container.innerHTML = '';
    Object.keys(RATIOS).forEach(key=>{
      const r = RATIOS[key];
      const card = document.createElement('div');
      card.className = 'ratio-card p-3';
      card.style.minWidth = '240px';
      card.style.flex = '0 0 auto';
      card.dataset.ratio = key;
      card.innerHTML = `
        <div class="d-flex align-items-start">
          <div class="me-3 ratio-abbr">${r.label.split(' ').map(w=>w[0]).slice(0,2).join('')}</div>
          <div>
            <div class="ratio-name">${r.label}</div>
            <div class="small-muted">${r.inputs.map(i=>i.label).join(' • ')}</div>
          </div>
        </div>
      `;
      card.addEventListener('click', function(){
        this.classList.toggle('selected');
        updateNextButton();
      });
      container.appendChild(card);
    });
  }

  /* Show selected ratios on Step 2 */
  document.getElementById('toStep2').addEventListener('click', function(){
    const selected = Array.from(document.querySelectorAll('#ratiosGrid .ratio-card.selected')).map(el=>el.dataset.ratio);
    if(selected.length === 0) return;
    const header = document.getElementById('selectedRatiosHeader');
    header.innerHTML = selected.map(k=>RATIOS[k].label).join(' • ');
    header.style.display = 'block';
    showStep(2);
    renderInputsFor(selected);
  });

  /* Stepper controls */
  function updateNextButton(){
    const sel = document.querySelectorAll('#ratiosGrid .ratio-card.selected');
    document.getElementById('toStep2').disabled = sel.length === 0;
  }

  document.getElementById('toStep2').addEventListener('click', function(){
    const selected = Array.from(document.querySelectorAll('#ratiosGrid .ratio-card.selected')).map(el=>el.dataset.ratio);
    if(selected.length === 0) return;
    showStep(2);
    renderInputsFor(selected);
  });

  document.getElementById('backToStep1').addEventListener('click', ()=> showStep(1));

  function showStep(n){
    document.querySelectorAll('#stepper .step').forEach(s => s.style.display = s.dataset.step==n ? 'block' : 'none');
    window.scrollTo({top:0, behavior:'smooth'});
  }

  /* Build inputs UI for selected ratios */
  function renderInputsFor(selectedKeys){
    const inputsContainer = document.getElementById('inputsContainer');
    inputsContainer.innerHTML = '';
    // gather unique inputs in order
    const seen = new Set();
    const fields = [];
    selectedKeys.forEach(key=>{
      RATIOS[key].inputs.forEach(inp=>{
        if(!seen.has(inp.k)){
          seen.add(inp.k);
          fields.push(inp);
        }
      });
    });

    fields.forEach(f=>{
      const col = document.createElement('div');
      col.className = 'col-md-6';
      col.innerHTML = `
        <label class="form-label">${f.label}</label>
        <input type="number" step="any" class="form-control ratio-input" name="${f.k}" id="input_${f.k}" placeholder="Enter ${f.label}">
      `;
      inputsContainer.appendChild(col);
    });

    // store selected keys for calculation
    inputsContainer.dataset.selected = JSON.stringify(selectedKeys);
  }

  /* Calculate button handler */
  document.getElementById('calcBtn').addEventListener('click', function(){
    const inputsContainer = document.getElementById('inputsContainer');
    const selected = JSON.parse(inputsContainer.dataset.selected || '[]');
    // gather values
    const values = {};
    let valid = true;
    document.querySelectorAll('.ratio-input').forEach(inp=>{
      const val = inp.value.trim();
      values[inp.name] = val === '' ? null : parseFloat(val);
    });

    // basic validation: ensure required fields are non-empty
    // identify any missing inputs
    const missing = [];
      selected.forEach(key=>{
        RATIOS[key].inputs.forEach(inp=>{
          if(values[inp.k] === null || isNaN(values[inp.k])) {
            missing.push(inp.label);
          }
        });
      });
      if(missing.length>0){
        alert('Please fill required inputs: ' + [...new Set(missing)].join(', '));
        return;
      }

    // compute results
    const results = [];
    selected.forEach(key=>{
      const r = RATIOS[key];
      const raw = r.formula(values);
      const formatted = (r.formatVal ? r.formatVal(raw) : formatNumber(raw));
      const status = r.thresholds(raw)[0];
      const actions = r.action(raw);
      results.push({key, label:r.label, raw, formatted, status, actions});
    });

    // render into modal and show immediately
    renderResultsModal(results, values);
    });

    /* Render results into modal */
    function renderResultsModal(results, inputValues){
      const c = document.getElementById('resultsContainer');
      c.innerHTML = '';

      // header block: timestamp and inputs summary
      const ts = new Date().toLocaleString();
      const header = document.createElement('div');
      header.className = 'mb-3';
      header.innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <h5 class="mb-0">Ratios Report</h5>
            <div class="small-muted">Generated: ${ts}</div>
          </div>
          <div class="text-end small-muted">
            <div><strong>OGMBC Consultants</strong></div>
            <div>Contact: info@ogmconsultants.com</div>
          </div>
        </div>
        <hr>
      `;
      c.appendChild(header);

      // show inputs summary
      const inputsBlock = document.createElement('div');
      let inputsHtml = '<div class="mb-3"><h6 class="mb-1">Inputs</h6><div class="row g-2">';
      Object.keys(inputValues).forEach(k=>{
        inputsHtml += `<div class="col-6"><div class="small-muted">${k.replace(/_/g,' ')}: <strong>${formatNumber(inputValues[k])}</strong></div></div>`;
      });
      inputsHtml += '</div></div>';
      inputsBlock.innerHTML = inputsHtml;
      c.appendChild(inputsBlock);

      // ratio cards
      const grid = document.createElement('div');
      grid.className = 'row g-3';
      results.forEach(r=>{
        const col = document.createElement('div');
        col.className = 'col-md-6';
        col.innerHTML = `
          <div class="p-3 rounded-3" style="background:#fff;">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="fw-semibold mb-1">${r.label}</div>
                <div class="small-muted">Value: <strong>${r.formatted}</strong></div>
              </div>
              <div class="text-end">
                ${severityBadge(r.status)}
                <div class="small-muted" style="font-size:.85rem;">${r.status === 'insufficient' ? 'Insufficient data' : (r.status==='good'?'Healthy':'See actions')}</div>
              </div>
            </div>
            <hr>
            <div>
              <strong>Recommended actions:</strong>
              <ul class="mb-0" style="color:#0f172a;">
                ${r.actions.map(a=>`<li>${a}</li>`).join('')}
              </ul>
            </div>
          </div>
        `;
        grid.appendChild(col);
      });
      c.appendChild(grid);

      // attach handlers for download and save
      document.getElementById('downloadPdfBtn').onclick = ()=> downloadPdf(results, inputValues);
      document.getElementById('saveResultBtn').onclick = ()=> saveResult(results, inputValues);

      // show modal
      const modalEl = document.getElementById('resultsModal');
      const modal = new bootstrap.Modal(modalEl, {backdrop:'static'});
      modal.show();

      // store last results for potential PDF / save
      window.lastResults = {timestamp: new Date().toISOString(), results, inputs: inputValues};
    }

    /* Generate PDF using jsPDF and html2canvas for the resultsContainer */
    async function downloadPdf(results, inputs){
      // create a temporary element with nicer paper styling
      const paper = document.createElement('div');
      paper.style.width = '800px';
      paper.style.padding = '24px';
      paper.style.background = '#fff';
      paper.style.color = '#0f172a';
      paper.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
          <div>
            <h3 style="margin:0;">OGMBC Consultants</h3>
            <div style="font-size:12px;color:#444;">Financial Ratios Report</div>
          </div>
          <div style="text-align:right;font-size:12px;color:#444;">
            Generated: ${new Date().toLocaleString()}
          </div>
        </div>
        <hr style="border-color:#eee;">
        <div style="margin-bottom:12px;">
          <strong>Inputs</strong>
          <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px;">
            ${Object.keys(inputs).map(k=>`<div style="min-width:180px;"><small style="color:#666;">${k.replace(/_/g,' ')}:</small><div><strong>${formatNumber(inputs[k])}</strong></div></div>`).join('')}
          </div>
        </div>
        <div>
          <strong>Results</strong>
          <table style="width:100%;border-collapse:collapse;margin-top:8px;">
            <thead><tr><th style="text-align:left;border-bottom:1px solid #eee;padding:6px 0">Ratio</th><th style="text-align:left;border-bottom:1px solid #eee;padding:6px 0">Value</th><th style="text-align:left;border-bottom:1px solid #eee;padding:6px 0">Status</th></tr></thead>
            <tbody>
              ${results.map(r=>`<tr><td style="padding:6px 0">${r.label}</td><td style="padding:6px 0">${r.formatted}</td><td style="padding:6px 0">${r.status}</td></tr>`).join('')}
            </tbody>
          </table>
        </div>
        <hr style="border-color:#eee;">
        <div style="font-size:12px;color:#666;">Generated by OGMBC Consultants — contact: info@ogmconsultants.com</div>
      `;

      document.body.appendChild(paper);
      // use html2canvas to render paper to image then to jsPDF
      const canvas = await html2canvas(paper, {scale:2, backgroundColor:'#ffffff'});
      const imgData = canvas.toDataURL('image/jpeg', 0.95);
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF({orientation:'portrait', unit:'px', format:[canvas.width, canvas.height]});
      pdf.addImage(imgData, 'JPEG', 0, 0, canvas.width, canvas.height);
      const name = `OGMBC_ratios_${new Date().toISOString().slice(0,19).replaceAll(':','-')}.pdf`;
      pdf.save(name);
      document.body.removeChild(paper);
    }

    /* Save results to localStorage */
    function saveResult(results, inputs){
      const store = JSON.parse(localStorage.getItem('ogmbc_saved_results') || '[]');
      const entry = {id: Date.now(), ts: new Date().toISOString(), inputs, results};
      store.push(entry);
      localStorage.setItem('ogmbc_saved_results', JSON.stringify(store));
      renderSaved();
      alert('Result saved locally (browser). You can compare saved results in the Saved Results area.');
    }

    /* Render saved results list */
    function renderSaved(){
      const area = document.getElementById('savedArea');
      const store = JSON.parse(localStorage.getItem('ogmbc_saved_results') || '[]');
      if(store.length === 0){
        area.innerHTML = '<div class="small-muted">No saved results yet.</div>';
        return;
      }
      let html = `<div class="table-responsive"><table class="table table-sm saved-table bg-white rounded-2"><thead><tr><th>When</th><th>Ratios</th><th>Actions</th></tr></thead><tbody>`;
      store.slice().reverse().forEach(s=>{
        html += `<tr>
          <td>${new Date(s.ts).toLocaleString()}</td>
          <td>${s.results.map(r=>`${r.label}: <strong>${r.formatted}</strong>`).join('<br>')}</td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick='viewSaved(${s.id})'>View</button>
            <button class="btn btn-sm btn-outline-danger" onclick='confirmDelete(${s.id})'>Delete</button>
          </td>
        </tr>`;
      });
      html += `</tbody></table></div>`;
      area.innerHTML = html;
    }

    /* view saved result */
    function viewSaved(id){
      const store = JSON.parse(localStorage.getItem('ogmbc_saved_results') || '[]');
      const entry = store.find(s=>s.id === id);
      if(!entry) return;
      renderResultsModal(entry.results, entry.inputs);
    }

    /* delete saved flow (with modal) */
    let pendingDeleteId = null;
    function confirmDelete(id){
      pendingDeleteId = id;
      const md = new bootstrap.Modal(document.getElementById('confirmDeleteSaved'));
      md.show();
    }
    document.getElementById('confirmDeleteYes').addEventListener('click', function(){
      const store = JSON.parse(localStorage.getItem('ogmbc_saved_results') || '[]');
      const filtered = store.filter(s=>s.id !== pendingDeleteId);
      localStorage.setItem('ogmbc_saved_results', JSON.stringify(filtered));
      renderSaved();
      pendingDeleteId = null;
      bootstrap.Modal.getInstance(document.getElementById('confirmDeleteSaved')).hide();
    });

    /* Clear saved */
    document.getElementById('clearSaved').addEventListener('click', function(){
      if(!confirm('Clear all saved results?')) return;
      localStorage.removeItem('ogmbc_saved_results');
      renderSaved();
    });

    /* Boot */
    renderRatioOptions();
    renderSaved();

    /* ensure modal z-index is higher than backdrop (already set in CSS), but ensure focus */
    document.getElementById('resultsModal').addEventListener('shown.bs.modal', function () {
    // ensure modal on top
    const modalEl = this;
    modalEl.style.zIndex = 99999;
    document.querySelectorAll('.modal-backdrop').forEach(b => b.style.zIndex = 99998);
  });
</script>