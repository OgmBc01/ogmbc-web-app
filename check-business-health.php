<?php
include 'includes/database.php';
include 'includes/header-1.php'
?> 

<!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
    <div class="container">
        <h1 class="display-4 fw-bold">Financial Ratios</h1>
        <h3>Numbers tell stories — and we help you read them.</h3>
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
      <p class="lead">OGM Business Consultants provides a complimentary ratio analysis snapshot that highlights key strengths, 
          red flags, and trend indicators using standard financial ratios across 7 comprehensive categories.
        </p>
        <div class="card card-app shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Financial Ratio Calculator</h3>
                </div>

                <!-- Stepper -->
                <div id="stepper" class="stepper mb-4">
                    <!-- STEP 1 -->
                    <div class="step" data-step="1">
                        <h5 class="mb-1">Step 1 — Select Ratio Category & Ratios</h5>
                        <p class="small-muted mb-2">Choose a category, then select one or more ratios to calculate.</p>
                        
                        <!-- Enhanced instructions -->
                        <div class="step-instruction">
                            <p class="text-dark"><i class="bi bi-info-circle instruction-icon"></i> Select a category tab, then click on ratio cards to select them. Selected ratios show a green checkmark.</p>
                        </div>

                        <!-- Category Tabs -->
                        <div class="ratio-category-tabs mb-4">
                            <div class="nav nav-pills nav-pills-category" id="categoryTabs" role="tablist">
                                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#liquidity">
                                    <i class="bi bi-cash-stack me-2"></i>Liquidity
                                </button>
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#solvency">
                                    <i class="bi bi-graph-up-arrow me-2"></i>Solvency & Leverage
                                </button>
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#profitability">
                                    <i class="bi bi-currency-dollar me-2"></i>Profitability
                                </button>
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#efficiency">
                                    <i class="bi bi-speedometer2 me-2"></i>Efficiency
                                </button>
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cashflow">
                                    <i class="bi bi-arrow-repeat me-2"></i>Cash Flow
                                </button>
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cost">
                                    <i class="bi bi-pie-chart me-2"></i>Cost Structure
                                </button>
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#customer">
                                    <i class="bi bi-people me-2"></i>Customer & Sales
                                </button>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content" id="categoryTabContent">
                            <!-- Liquidity -->
                            <div class="tab-pane fade show active" id="liquidity">
                                <div id="ratiosGridLiquidity" class="ratios-grid"></div>
                            </div>
                            <!-- Solvency & Leverage -->
                            <div class="tab-pane fade" id="solvency">
                                <div id="ratiosGridSolvency" class="ratios-grid"></div>
                            </div>
                            <!-- Profitability -->
                            <div class="tab-pane fade" id="profitability">
                                <div id="ratiosGridProfitability" class="ratios-grid"></div>
                            </div>
                            <!-- Efficiency -->
                            <div class="tab-pane fade" id="efficiency">
                                <div id="ratiosGridEfficiency" class="ratios-grid"></div>
                            </div>
                            <!-- Cash Flow -->
                            <div class="tab-pane fade" id="cashflow">
                                <div id="ratiosGridCashflow" class="ratios-grid"></div>
                            </div>
                            <!-- Cost Structure -->
                            <div class="tab-pane fade" id="cost">
                                <div id="ratiosGridCost" class="ratios-grid"></div>
                            </div>
                            <!-- Customer & Sales -->
                            <div class="tab-pane fade" id="customer">
                                <div id="ratiosGridCustomer" class="ratios-grid"></div>
                            </div>
                        </div>

                        <div class="selected-ratios-summary mt-4 p-3 rounded-3" style="background:#0b1224; display:none;">
                            <h6 class="mb-2" style="color:var(--gold);"><i class="bi bi-check-circle-fill me-2"></i>Selected Ratios</h6>
                            <div id="selectedRatiosList" class="selected-ratios-list"></div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <a id="toStep2" class="btn btn-primary" disabled>
                                Next: Enter Inputs 
                                <span id="selectedCount" class="selected-counter" style="display: none;">0</span>
                            </a>
                        </div>
                    </div>

                    <!-- STEP 2 -->
                    <div class="step" data-step="2" style="display:none;">
                        <h5 class="mb-1">Step 2 — Enter Financial Inputs</h5>
                        <p class="small-muted mb-2">Only fields required for your selected ratios are shown below.</p>
                        
                        <!-- Enhanced instructions -->
                        <div class="step-instruction">
                            <p class="text-dark"><i class="bi bi-pencil-square instruction-icon"></i> Fill in all required financial values in their respective sections. All fields marked with * are required.</p>
                        </div>

                        <!-- Selected ratios display -->
                        <div id="selectedRatiosHeader" class="p-3 mb-3 rounded-3" style="background:#0b1224; color:var(--gold); font-weight:600; display:none;">
                        </div>

                        <form id="inputsForm" novalidate>
                            <!-- Inputs will be grouped by category -->
                            <div id="inputsContainer" class="inputs-container"></div>

                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <button id="backToStep1" type="button" class="btn btn-secondary">← Back to Ratio Selection</button>
                                <div>
                                    <button id="calcBtn" type="button" class="btn btn-gold btn-lg" style="background:var(--gold); color:var(--primary);">
                                        <i class="bi bi-calculator me-2"></i>Calculate Ratios
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <hr class="border-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><i class="bi bi-save me-2"></i>Saved Results</h6>
                    <div><a id="clearSaved" class="btn btn-sm btn-secondary">Clear All Saved</a></div>
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
                            <h5 class="modal-title">Financial Ratios Analysis Report</h5>
                            <div class="small-muted">OGMBC Consultants — Comprehensive Financial Assessment</div>
                        </div>
                        <div class="ms-auto">
                            <button id="saveResultBtn" class="btn btn-sm btn-outline-primary me-2">
                                <i class="bi bi-save me-1"></i>Save
                            </button>
                            <button id="downloadPdfBtn" class="btn btn-sm btn-outline-success me-2">
                                <i class="bi bi-file-pdf me-1"></i>PDF
                            </button>
                            <a id="contactOgm" class="btn btn-sm btn-dark" href="mailto:info@ogmconsultants.com?subject=Help%20with%20financial%20ratios" target="_blank">
                                <i class="bi bi-envelope me-1"></i>Contact OGM
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="modal-body bg-paper">
                        <div id="resultsContainer"></div>
                    </div>
                    <div class="modal-footer">
                        <small class="text-muted me-auto">
                            <i class="bi bi-lightbulb me-1"></i>Save results to compare periods or download PDF for records. 
                            <a href="contact.php" style="color: var(--gold)">Contact OGMBC</a> for detailed analysis.
                        </small>
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

<?php include 'includes/footer.php' ?>

<script>
  /* =========================
    Enhanced Ratio Definitions with 7 Categories
    ========================= */
  const RATIOS = {
    // 1. Liquidity Ratios
    current_ratio: {
      label: "Current Ratio",
      category: "liquidity",
      inputs: [
        { k: "current_assets", label: "Current Assets" },
        { k: "current_liabilities", label: "Current Liabilities" }
      ],
      formula: (v) => (v.current_liabilities === 0 ? null : v.current_assets / v.current_liabilities),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 2.0 ? ["good"] : val >= 1.2 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data — cannot divide by zero."];
        if (val > 2.0) return ["No immediate action; maintain working capital discipline."];
        if (val >= 1.2) return ["Improve receivables collection, negotiate payables, manage inventory."];
        return ["Urgent: obtain short-term funding; accelerate collections; reduce discretionary spend."];
      },
      description: "Broad 'can we cover short-term bills?' test"
    },

    quick_ratio: {
      label: "Quick Ratio (Acid Test)",
      category: "liquidity",
      inputs: [
        { k: "current_assets", label: "Current Assets" },
        { k: "inventory", label: "Inventory" },
        { k: "current_liabilities", label: "Current Liabilities" }
      ],
      formula: (v) => (v.current_liabilities === 0 ? null : (v.current_assets - v.inventory) / v.current_liabilities),
      thresholds: (val) => (val === null ? ["insufficient"] : val >= 1.0 ? ["good"] : val >= 0.7 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data — cannot divide by zero."];
        if (val >= 1.0) return ["Strong liquidity position; maintain current policies."];
        if (val >= 0.7) return ["Consider short-term credit facilities; improve collections."];
        return ["Urgent: restructure short-term liabilities; seek immediate funding."];
      },
      description: "Stricter test using liquid assets only (cash + receivables)"
    },

    cash_ratio: {
      label: "Cash Ratio",
      category: "liquidity",
      inputs: [
        { k: "cash_equivalents", label: "Cash + Equivalents" },
        { k: "current_liabilities", label: "Current Liabilities" }
      ],
      formula: (v) => (v.current_liabilities === 0 ? null : v.cash_equivalents / v.current_liabilities),
      thresholds: (val) => (val === null ? ["insufficient"] : val >= 0.5 ? ["good"] : val >= 0.2 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data — cannot divide by zero."];
        if (val >= 0.5) return ["Very safe position; consider investing excess cash."];
        if (val >= 0.2) return ["Monitor cash reserves; improve collections."];
        return ["Risky if collections slow; build cash reserves urgently."];
      },
      description: "Most conservative 'immediate payment' ability"
    },

    operating_cash_flow_ratio: {
      label: "Operating Cash Flow Ratio",
      category: "liquidity",
      inputs: [
        { k: "operating_cash_flow", label: "Operating Cash Flow (CFO)" },
        { k: "current_liabilities", label: "Current Liabilities" }
      ],
      formula: (v) => (v.current_liabilities === 0 ? null : v.operating_cash_flow / v.current_liabilities),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 1.0 ? ["good"] : val >= 0.5 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data — cannot divide by zero."];
        if (val > 1.0) return ["Strong cash flow vs short-term obligations."];
        if (val >= 0.5) return ["Improve operational cash collection; monitor closely."];
        return ["Cash flow risk — focus on working capital relief and external funding."];
      },
      description: "Cash reality check: operations generate enough cash to cover current liabilities"
    },

    // 2. Solvency & Leverage Ratios
    debt_to_equity: {
      label: "Debt-to-Equity (D/E)",
      category: "solvency",
      inputs: [
        { k: "total_debt", label: "Total Debt" },
        { k: "shareholders_equity", label: "Shareholders' Equity" }
      ],
      formula: (v) => (v.shareholders_equity === 0 ? null : v.total_debt / v.shareholders_equity),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 1.0 ? ["good"] : val <= 2.0 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Equity zero)."];
        if (val < 1.0) return ["Conservative leverage; consider strategic debt for growth."];
        if (val <= 2.0) return ["Monitor leverage and interest costs; consider deleveraging."];
        return ["High leverage — urgent refinancing or equity injection needed."];
      },
      description: "Measures how debt-funded you are versus owners' capital"
    },

    debt_ratio: {
      label: "Debt Ratio",
      category: "solvency",
      inputs: [
        { k: "total_liabilities", label: "Total Liabilities" },
        { k: "total_assets", label: "Total Assets" }
      ],
      formula: (v) => (v.total_assets === 0 ? null : v.total_liabilities / v.total_assets),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 0.4 ? ["good"] : val <= 0.6 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Assets zero)."];
        if (val < 0.4) return ["Strong solvency buffer; maintain prudent financing."];
        if (val <= 0.6) return ["Normal range; monitor earnings and cash flow stability."];
        return ["High stress if earnings dip; reduce liabilities or increase equity."];
      },
      description: "Share of assets financed by liabilities"
    },

    equity_ratio: {
      label: "Equity Ratio",
      category: "solvency",
      inputs: [
        { k: "shareholders_equity", label: "Shareholders' Equity" },
        { k: "total_assets", label: "Total Assets" }
      ],
      formula: (v) => (v.total_assets === 0 ? null : v.shareholders_equity / v.total_assets),
      thresholds: (val) => (val === null ? ["insufficient"] : val >= 0.5 ? ["good"] : val >= 0.3 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Assets zero)."];
        if (val >= 0.5) return ["Strong equity cushion; focus on growth."];
        if (val >= 0.3) return ["Adequate but monitor; small losses can create trouble."];
        return ["Thin equity — urgent need to increase equity or reduce liabilities."];
      },
      description: "Share of assets funded by equity; higher = more resilience"
    },

    interest_coverage: {
      label: "Interest Coverage Ratio",
      category: "solvency",
      inputs: [
        { k: "ebit", label: "EBIT" },
        { k: "interest_expense", label: "Interest Expense" }
      ],
      formula: (v) => (v.interest_expense === 0 ? null : v.ebit / v.interest_expense),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 5.0 ? ["good"] : val >= 2.0 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Interest expense zero)."];
        if (val > 5.0) return ["Comfortable interest coverage; strong operational performance."];
        if (val >= 2.0) return ["Tight coverage — consider reducing interest or boosting EBIT."];
        return ["Critical: EBIT doesn't cover interest — urgent refinancing or cost reduction."];
      },
      description: "Ability to pay interest from operating profit (TIE)"
    },

    dscr: {
      label: "Debt Service Coverage Ratio (DSCR)",
      category: "solvency",
      inputs: [
        { k: "operating_cash_flow", label: "Operating Cash Flow" },
        { k: "total_debt_service", label: "Total Debt Service" }
      ],
      formula: (v) => (v.total_debt_service === 0 ? null : v.operating_cash_flow / v.total_debt_service),
      thresholds: (val) => (val === null ? ["insufficient"] : val >= 1.25 ? ["good"] : val >= 1.0 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Debt service zero)."];
        if (val >= 1.25) return ["Healthy coverage; lenders comfortable."];
        if (val >= 1.0) return ["Tight coverage — improve cash flow or restructure debt."];
        return ["Deficit: not enough cash to meet payments — urgent restructuring."];
      },
      description: "Ability to cover scheduled debt payments from cash flow"
    },

    leverage_ratio: {
      label: "Leverage Ratio (Assets/Equity)",
      category: "solvency",
      inputs: [
        { k: "total_assets", label: "Total Assets" },
        { k: "shareholders_equity", label: "Shareholders' Equity" }
      ],
      formula: (v) => (v.shareholders_equity === 0 ? null : v.total_assets / v.shareholders_equity),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 2.0 ? ["good"] : val <= 4.0 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Equity zero)."];
        if (val < 2.0) return ["Low leverage; conservative structure."];
        if (val <= 4.0) return ["Moderate leverage; monitor asset efficiency."];
        return ["High leverage — small hits to asset value/profit can be damaging."];
      },
      description: "How much asset base is supported by each 1 of equity"
    },

    // 3. Profitability Ratios
    gross_margin: {
      label: "Gross Profit Margin",
      category: "profitability",
      inputs: [
        { k: "revenue", label: "Revenue" },
        { k: "cogs", label: "Cost of Goods Sold (COGS)" }
      ],
      formula: (v) => (v.revenue === 0 ? null : (v.revenue - v.cogs) / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.40 ? ["good"] : val >= 0.20 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val > 0.40) return ["Strong pricing power; maintain cost discipline."];
        if (val >= 0.20) return ["Review pricing, supplier costs; improve product mix."];
        return ["Consider price increases, cost-cutting, or product rationalization."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "% left after direct costs — reflects pricing power and cost of delivery"
    },

    operating_margin: {
      label: "Operating Margin",
      category: "profitability",
      inputs: [
        { k: "ebit", label: "EBIT" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : v.ebit / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.15 ? ["good"] : val >= 0.05 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val > 0.15) return ["Excellent operational efficiency; continue scale/discipline."];
        if (val >= 0.05) return ["Improve overhead control; review operating expenses."];
        return ["Operations are loss-making — urgent cost reduction or revenue increase."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "% profit from core operations after operating expenses"
    },

    ebitda_margin: {
      label: "EBITDA Margin",
      category: "profitability",
      inputs: [
        { k: "ebitda", label: "EBITDA" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : v.ebitda / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.20 ? ["good"] : val >= 0.10 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val > 0.20) return ["Strong operating efficiency; continue current strategy."];
        if (val >= 0.10) return ["Improve operating efficiency; review product mix."];
        return ["Low margin — evaluate operating structure and cost base."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Operating profit before depreciation/amortization"
    },

    net_margin: {
      label: "Net Profit Margin",
      category: "profitability",
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
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Bottom-line % after all costs (including interest and taxes)"
    },

    roa: {
      label: "Return on Assets (ROA)",
      category: "profitability",
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
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "How efficiently assets generate net profit"
    },

    roe: {
      label: "Return on Equity (ROE)",
      category: "profitability",
      inputs: [
        { k: "net_income", label: "Net Income" },
        { k: "shareholders_equity", label: "Shareholders' Equity" }
      ],
      formula: (v) => (v.shareholders_equity === 0 ? null : v.net_income / v.shareholders_equity),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.15 ? ["good"] : val >= 0.08 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Equity zero)."];
        if (val > 0.15) return ["Strong returns for equity holders; maintain strategy."];
        if (val >= 0.08) return ["Moderate returns; optimize leverage and profitability."];
        return ["Low returns; review capital allocation strategy."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Return earned for shareholders on their equity"
    },

    roic: {
      label: "Return on Invested Capital (ROIC)",
      category: "profitability",
      inputs: [
        { k: "nopat", label: "NOPAT (Net Operating Profit After Tax)" },
        { k: "invested_capital", label: "Invested Capital" }
      ],
      formula: (v) => (v.invested_capital === 0 ? null : v.nopat / v.invested_capital),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.10 ? ["good"] : val >= 0.06 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Invested capital zero)."];
        if (val > 0.10) return ["Value creation: ROIC > cost of capital."];
        if (val >= 0.06) return ["Neutral: review capital allocation."];
        return ["Value destruction: ROIC < cost of capital — urgent review."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "After-tax operating return generated by capital invested in operations"
    },

    // 4. Efficiency Ratios
    asset_turnover: {
      label: "Asset Turnover",
      category: "efficiency",
      inputs: [
        { k: "revenue", label: "Revenue" },
        { k: "total_assets", label: "Total Assets" }
      ],
      formula: (v) => (v.total_assets === 0 ? null : v.revenue / v.total_assets),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 1.0 ? ["good"] : val >= 0.5 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Total assets zero)."];
        if (val > 1.0) return ["High efficiency — scale operations."];
        if (val >= 0.5) return ["Improve asset use & sales productivity."];
        return ["Low utilization — review asset base."];
      },
      description: "How efficiently total assets generate sales"
    },

    fixed_asset_turnover: {
      label: "Fixed Asset Turnover",
      category: "efficiency",
      inputs: [
        { k: "revenue", label: "Revenue" },
        { k: "net_fixed_assets", label: "Net Fixed Assets" }
      ],
      formula: (v) => (v.net_fixed_assets === 0 ? null : v.revenue / v.net_fixed_assets),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 3.0 ? ["good"] : val >= 1.5 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Fixed assets zero)."];
        if (val > 3.0) return ["Excellent PP&E utilization."];
        if (val >= 1.5) return ["Adequate utilization; monitor capacity."];
        return ["Idle capacity/overinvestment — review asset base."];
      },
      description: "How effectively PP&E generates sales"
    },

    inventory_turnover: {
      label: "Inventory Turnover",
      category: "efficiency",
      inputs: [
        { k: "cogs", label: "Cost of Goods Sold (COGS)" },
        { k: "avg_inventory", label: "Average Inventory" }
      ],
      formula: (v) => (v.avg_inventory === 0 ? null : v.cogs / v.avg_inventory),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 6.0 ? ["good"] : val >= 3.0 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Average inventory zero)."];
        if (val > 6.0) return ["Strong turnover; keep replenishment tight."];
        if (val >= 3.0) return ["Optimize inventory; remove slow movers."];
        return ["Too slow — discounting, improve forecasting, JIT strategies."];
      },
      description: "How quickly inventory is sold and replaced"
    },

    days_inventory_outstanding: {
      label: "Days Inventory Outstanding (DIO)",
      category: "efficiency",
      inputs: [
        { k: "avg_inventory", label: "Average Inventory" },
        { k: "cogs", label: "COGS" }
      ],
      formula: (v) => (v.cogs === 0 ? null : (v.avg_inventory / v.cogs) * 365),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 45 ? ["good"] : val <= 90 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (COGS zero)."];
        if (val < 45) return ["Lean inventory management."];
        if (val <= 90) return ["Monitor inventory levels; reduce slow-moving items."];
        return ["Cash tied up/obsolete risk — urgent inventory reduction."];
      },
      formatVal: (v) => (v === null ? "N/A" : Math.round(v) + " days"),
      description: "Average days inventory sits before sale"
    },

    receivables_turnover: {
      label: "Receivables Turnover",
      category: "efficiency",
      inputs: [
        { k: "credit_sales", label: "Credit Sales" },
        { k: "avg_accounts_receivable", label: "Average Accounts Receivable" }
      ],
      formula: (v) => (v.avg_accounts_receivable === 0 ? null : v.credit_sales / v.avg_accounts_receivable),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 8.0 ? ["good"] : val >= 4.0 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Receivables zero)."];
        if (val > 8.0) return ["Strong collections; maintain policies."];
        if (val >= 4.0) return ["Improve collections; review credit terms."];
        return ["Slow payments/weak credit control — urgent collections effort."];
      },
      description: "How fast customers pay"
    },

    dso: {
      label: "Days Sales Outstanding (DSO)",
      category: "efficiency",
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
      formatVal: (v) => (v === null ? "N/A" : Math.round(v) + " days"),
      description: "Average days to collect cash from sales"
    },

    payables_turnover: {
      label: "Payables Turnover",
      category: "efficiency",
      inputs: [
        { k: "cogs", label: "COGS" },
        { k: "avg_accounts_payable", label: "Average Accounts Payable" }
      ],
      formula: (v) => (v.avg_accounts_payable === 0 ? null : v.cogs / v.avg_accounts_payable),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 6.0 ? ["good"] : val <= 12.0 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Payables zero)."];
        if (val < 6.0) return ["Using supplier financing effectively."];
        if (val <= 12.0) return ["Monitor payment terms; balance cash flow."];
        return ["Paying too quickly — may miss free supplier financing."];
      },
      description: "How fast you pay suppliers"
    },

    dpo: {
      label: "Days Payables Outstanding (DPO)",
      category: "efficiency",
      inputs: [
        { k: "avg_accounts_payable", label: "Average Accounts Payable" },
        { k: "cogs", label: "COGS" }
      ],
      formula: (v) => (v.cogs === 0 ? null : (v.avg_accounts_payable / v.cogs) * 365),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 60 ? ["good"] : val >= 30 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (COGS zero)."];
        if (val > 60) return ["Preserves cash but monitor supplier relationships."];
        if (val >= 30) return ["Balanced approach; maintain good terms."];
        return ["Paying too fast — strains cash; negotiate better terms."];
      },
      formatVal: (v) => (v === null ? "N/A" : Math.round(v) + " days"),
      description: "Average days you take to pay suppliers"
    },

    // 5. Cash Flow Ratios
    cash_conversion_cycle: {
      label: "Cash Conversion Cycle (CCC)",
      category: "cashflow",
      inputs: [
        { k: "dio", label: "DIO (Days)" },
        { k: "dso", label: "DSO (Days)" },
        { k: "dpo", label: "DPO (Days)" }
      ],
      formula: (v) => (v.dio + v.dso - v.dpo),
      thresholds: (val) => (val < 30 ? ["good"] : val <= 60 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val < 30) return ["Excellent cash conversion; maintain current practices."];
        if (val <= 60) return ["Monitor working capital; improve collection/payment timing."];
        return ["Cash trapped longer — urgent working capital optimization needed."];
      },
      formatVal: (v) => Math.round(v) + " days",
      description: "Days cash is tied up in operations (DIO + DSO - DPO)"
    },

    working_capital: {
      label: "Working Capital",
      category: "cashflow",
      inputs: [
        { k: "current_assets", label: "Current Assets" },
        { k: "current_liabilities", label: "Current Liabilities" }
      ],
      formula: (v) => (v.current_assets - v.current_liabilities),
      thresholds: (val) => (val > 0 ? ["good"] : ["risk"]),
      action: (val) => {
        if (val > 0) return ["Positive buffer; optimize for efficiency."];
        return ["Negative working capital — potential liquidity pressure."];
      },
      description: "Short-term liquidity buffer (CA - CL)"
    },

    working_capital_ratio: {
      label: "Working Capital Ratio (WC/Revenue)",
      category: "cashflow",
      inputs: [
        { k: "working_capital", label: "Working Capital" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : v.working_capital / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 0.2 ? ["good"] : val <= 0.4 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val < 0.2) return ["Efficient working capital management."];
        if (val <= 0.4) return ["Monitor working capital intensity."];
        return ["Too much cash tied up — improve efficiency."];
      },
      description: "How working-capital intensive the business is"
    },

    operating_cash_flow_margin: {
      label: "Operating Cash Flow Margin",
      category: "cashflow",
      inputs: [
        { k: "operating_cash_flow", label: "Operating Cash Flow (CFO)" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : v.operating_cash_flow / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.10 ? ["good"] : val >= 0.05 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val > 0.10) return ["Strong cash conversion; maintain current operations."];
        if (val >= 0.05) return ["Improve working capital management."];
        return ["Weak cash conversion — review operations and collections."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "How well sales turn into operating cash"
    },

    free_cash_flow: {
      label: "Free Cash Flow (FCF)",
      category: "cashflow",
      inputs: [
        { k: "operating_cash_flow", label: "Operating Cash Flow (CFO)" },
        { k: "capex", label: "Capital Expenditures (Capex)" }
      ],
      formula: (v) => (v.operating_cash_flow - v.capex),
      thresholds: (val) => (val > 0 ? ["good"] : ["risk"]),
      action: (val) => {
        if (val > 0) return ["Self-funding business; good for growth/deleveraging."];
        return ["Cash-consuming — acceptable if high-ROI growth capex, otherwise risky."];
      },
      description: "Cash left after capital spending"
    },

    fcf_margin: {
      label: "Free Cash Flow Margin",
      category: "cashflow",
      inputs: [
        { k: "free_cash_flow", label: "Free Cash Flow" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : v.free_cash_flow / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.05 ? ["good"] : val >= 0 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val > 0.05) return ["Strong cash economics; continue strategy."];
        if (val >= 0) return ["Monitor cash flow; ensure capex delivers returns."];
        return ["Cash-consuming business unless deliberate value-accretive expansion."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Free cash generated per unit of sales"
    },

    // 6. Cost Structure Ratios
    cogs_percentage: {
      label: "COGS % of Sales",
      category: "cost",
      inputs: [
        { k: "cogs", label: "COGS" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : v.cogs / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 0.6 ? ["good"] : val <= 0.8 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val < 0.6) return ["Good direct cost control; maintain efficiency."];
        if (val <= 0.8) return ["Review input costs, waste, discounting, or purchasing."];
        return ["High cost intensity — urgent cost reduction or pricing review."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Direct cost intensity; higher % = lower gross margin"
    },

    sga_percentage: {
      label: "SG&A % of Sales",
      category: "cost",
      inputs: [
        { k: "sga", label: "SG&A Expenses" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : v.sga / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 0.25 ? ["good"] : val <= 0.35 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val < 0.25) return ["Good overhead control; operating leverage improving."];
        if (val <= 0.35) return ["Monitor overhead growth vs revenue."];
        return ["Overhead growing too fast — urgent cost control needed."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Overhead burden versus sales"
    },

    staff_cost_percentage: {
      label: "Staff Cost % of Sales",
      category: "cost",
      inputs: [
        { k: "staff_costs", label: "Staff Costs" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : v.staff_costs / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 0.3 ? ["good"] : val <= 0.4 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val < 0.3) return ["Good labor productivity; maintain."];
        if (val <= 0.4) return ["Review staffing levels, overtime, pricing."];
        return ["Labor intensity too high — optimize staffing or increase prices."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Labor intensity relative to sales"
    },

    rent_percentage: {
      label: "Rent/Occupancy %",
      category: "cost",
      inputs: [
        { k: "rent_expense", label: "Rent Expense" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : v.rent_expense / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 0.1 ? ["good"] : val <= 0.15 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val < 0.1) return ["Good location cost management."];
        if (val <= 0.15) return ["Monitor rent vs sales; consider renegotiation."];
        return ["High occupancy cost — profitability sensitive to sales dips."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Location cost pressure (key for retail/F&B/branches)"
    },

    admin_expense_ratio: {
      label: "Admin Expense Ratio",
      category: "cost",
      inputs: [
        { k: "admin_expenses", label: "Admin Expenses" },
        { k: "revenue", label: "Revenue" }
      ],
      formula: (v) => (v.revenue === 0 ? null : v.admin_expenses / v.revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 0.15 ? ["good"] : val <= 0.25 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Revenue zero)."];
        if (val < 0.15) return ["Efficient admin processes; good leverage."];
        if (val <= 0.25) return ["Review process efficiency; control admin bloat."];
        return ["Admin overhead too high — urgent process optimization."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Admin overhead load relative to sales"
    },

    // 7. Customer & Sales Ratios
    revenue_growth: {
      label: "Revenue Growth %",
      category: "customer",
      inputs: [
        { k: "revenue_current", label: "Current Period Revenue" },
        { k: "revenue_previous", label: "Previous Period Revenue" }
      ],
      formula: (v) => (v.revenue_previous === 0 ? null : (v.revenue_current - v.revenue_previous) / v.revenue_previous),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0.10 ? ["good"] : val >= 0 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Previous revenue zero)."];
        if (val > 0.10) return ["Strong growth; confirm it's profitable and collectible."];
        if (val >= 0) return ["Stable but watch costs and market position."];
        return ["Contraction — review pricing, churn, market conditions."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Measures top-line change versus last period"
    },

    gross_profit_per_customer: {
      label: "Gross Profit per Customer",
      category: "customer",
      inputs: [
        { k: "gross_profit", label: "Gross Profit" },
        { k: "customer_count", label: "Customer Count" }
      ],
      formula: (v) => (v.customer_count === 0 ? null : v.gross_profit / v.customer_count),
      thresholds: (val) => (val === null ? ["insufficient"] : val > 0 ? ["good"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Customer count zero)."];
        if (val > 0) return ["Monitor trends for pricing/mix or cost-to-serve changes."];
        return ["Negative gross profit per customer — urgent pricing/cost review."];
      },
      description: "Average gross profit contribution per customer"
    },

    ar_concentration: {
      label: "AR Concentration %",
      category: "customer",
      inputs: [
        { k: "top_customer_ar", label: "Top Customer Accounts Receivable" },
        { k: "total_ar", label: "Total Accounts Receivable" }
      ],
      formula: (v) => (v.total_ar === 0 ? null : v.top_customer_ar / v.total_ar),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 0.10 ? ["good"] : val <= 0.20 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Total AR zero)."];
        if (val < 0.10) return ["Diversified and resilient receivables."];
        if (val <= 0.20) return ["Monitor concentration; diversify customer base."];
        return ["High concentration — one late payer can hurt liquidity."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Cash-flow and credit-risk exposure to one customer"
    },

    customer_concentration: {
      label: "Customer Concentration %",
      category: "customer",
      inputs: [
        { k: "top_customer_revenue", label: "Top Customer Revenue" },
        { k: "total_revenue", label: "Total Revenue" }
      ],
      formula: (v) => (v.total_revenue === 0 ? null : v.top_customer_revenue / v.total_revenue),
      thresholds: (val) => (val === null ? ["insufficient"] : val < 0.10 ? ["good"] : val <= 0.20 ? ["warn"] : ["risk"]),
      action: (val) => {
        if (val === null) return ["Insufficient data (Total revenue zero)."];
        if (val < 0.10) return ["Low dependence; good stability."];
        if (val <= 0.20) return ["Moderate concentration; consider diversification."];
        return ["High dependence on one customer — stability/valuation risk."];
      },
      formatVal: (v) => (v === null ? "N/A" : (v * 100).toFixed(2) + "%"),
      description: "Dependence on one customer for sales"
    }
  };

  /* Helper utilities */
  const formatNumber = (n) => {
    if (n === null || n === undefined) return 'N/A';
    if (typeof n === 'number') {
      if (Math.abs(n) >= 1000000) return (n / 1000000).toFixed(2) + 'M';
      if (Math.abs(n) >= 1000) return (n / 1000).toFixed(2) + 'K';
      return n.toLocaleString(undefined, {maximumFractionDigits:2});
    }
    return n;
  };
  
  const severityBadge = status => {
    if (status==='good') return '<span class="badge badge-good me-1">Good</span>';
    if (status==='warn') return '<span class="badge badge-warn me-1">Warning</span>';
    if (status==='risk') return '<span class="badge badge-risk me-1">Risk</span>';
    return '<span class="badge bg-secondary me-1">N/A</span>';
  };

  /* ===== RENDER RATIO OPTIONS BY CATEGORY ===== */
  function renderRatioOptionsByCategory(){
    const categories = {
      liquidity: document.getElementById('ratiosGridLiquidity'),
      solvency: document.getElementById('ratiosGridSolvency'),
      profitability: document.getElementById('ratiosGridProfitability'),
      efficiency: document.getElementById('ratiosGridEfficiency'),
      cashflow: document.getElementById('ratiosGridCashflow'),
      cost: document.getElementById('ratiosGridCost'),
      customer: document.getElementById('ratiosGridCustomer')
    };

    Object.keys(categories).forEach(category => {
      const container = categories[category];
      container.innerHTML = '';
      
      Object.keys(RATIOS)
        .filter(key => RATIOS[key].category === category)
        .forEach(key => {
          const r = RATIOS[key];
          const card = document.createElement('div');
          card.className = 'ratio-card';
          card.dataset.ratio = key;
          card.dataset.category = category;
          card.innerHTML = `
            <div class="selected-tick">
              <i class="bi bi-check-lg"></i>
            </div>
            <div class="d-flex align-items-start">
              <div class="me-3 ratio-abbr">${r.label.split(' ').map(w=>w[0]).slice(0,2).join('')}</div>
              <div class="flex-grow-1">
                <div class="ratio-name">${r.label}</div>
                <div class="ratio-description">${r.description || ''}</div>
                <div class="small-muted mt-1" style="font-size:0.75rem;">
                  <i class="bi bi-calculator me-1"></i>${r.inputs.map(i=>i.label).join(' • ')}
                </div>
              </div>
            </div>
          `;
          card.addEventListener('click', function(){
            this.classList.toggle('selected');
            updateSelectedCounter();
            updateSelectedRatiosList();
            updateNextButton();
          });
          container.appendChild(card);
        });
    });
  }

  /* Update selected ratios list */
  function updateSelectedRatiosList() {
    const selected = getAllSelectedRatios();
    const listContainer = document.getElementById('selectedRatiosList');
    const summaryContainer = document.querySelector('.selected-ratios-summary');
    
    if (selected.length === 0) {
      listContainer.innerHTML = '<div class="text-muted small">No ratios selected</div>';
      summaryContainer.style.display = 'none';
      return;
    }
    
    // Group by category
    const byCategory = {};
    selected.forEach(ratioKey => {
      const ratio = RATIOS[ratioKey];
      if (!byCategory[ratio.category]) {
        byCategory[ratio.category] = [];
      }
      byCategory[ratio.category].push(ratio.label);
    });
    
    let html = '';
    Object.keys(byCategory).forEach(category => {
      const categoryName = category.charAt(0).toUpperCase() + category.slice(1);
      html += `<div class="mb-2"><strong>${categoryName}:</strong> ${byCategory[category].join(', ')}</div>`;
    });
    
    listContainer.innerHTML = html;
    summaryContainer.style.display = 'block';
  }

  /* Get all selected ratios across all categories */
  function getAllSelectedRatios() {
    const selected = [];
    document.querySelectorAll('.ratio-card.selected').forEach(card => {
      selected.push(card.dataset.ratio);
    });
    return selected;
  }

  /* Update selected counter */
  function updateSelectedCounter() {
    const selectedCount = document.querySelectorAll('.ratio-card.selected').length;
    const counter = document.getElementById('selectedCount');
    
    if (selectedCount > 0) {
      counter.textContent = selectedCount;
      counter.style.display = 'inline-flex';
    } else {
      counter.style.display = 'none';
    }
  }

  /* Show selected ratios on Step 2 */
  document.getElementById('toStep2').addEventListener('click', function(){
    const selected = getAllSelectedRatios();
    if(selected.length === 0) return;
    
    const header = document.getElementById('selectedRatiosHeader');
    const ratioNames = selected.map(k => RATIOS[k].label).join(' • ');
    header.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i>Selected Ratios: ${ratioNames}`;
    header.style.display = 'block';
    
    showStep(2);
    renderInputsFor(selected);
  });

  /* Stepper controls */
  function updateNextButton(){
    const sel = document.querySelectorAll('.ratio-card.selected');
    document.getElementById('toStep2').disabled = sel.length === 0;
  }

  document.getElementById('backToStep1').addEventListener('click', ()=> showStep(1));

  function showStep(n){
    document.querySelectorAll('#stepper .step').forEach(s => s.style.display = s.dataset.step==n ? 'block' : 'none');
    window.scrollTo({top:0, behavior:'smooth'});
  }

  /* Build inputs UI for selected ratios */
  function renderInputsFor(selectedKeys){
    const inputsContainer = document.getElementById('inputsContainer');
    inputsContainer.innerHTML = '';
    
    // Group inputs by category for better organization
    const inputsByCategory = {};
    const seen = new Set();
    
    selectedKeys.forEach(key=>{
      const ratio = RATIOS[key];
      const category = ratio.category;
      
      if (!inputsByCategory[category]) {
        inputsByCategory[category] = [];
      }
      
      ratio.inputs.forEach(inp=>{
        if(!seen.has(inp.k)){
          seen.add(inp.k);
          inputsByCategory[category].push(inp);
        }
      });
    });

    // Render category sections
    Object.keys(inputsByCategory).forEach(category => {
      if (inputsByCategory[category].length === 0) return;
      
      const categoryName = category.charAt(0).toUpperCase() + category.slice(1);
      const section = document.createElement('div');
      section.className = 'input-category-section mb-4';
      section.innerHTML = `
        <h6 class="mb-3" style="color:var(--gold); border-bottom:2px solid var(--gold); padding-bottom:5px;">
          <i class="bi bi-folder me-2"></i>${categoryName} Inputs
        </h6>
        <div class="row " id="inputs_${category}"></div>
      `;
      inputsContainer.appendChild(section);
      
      // Render inputs for this category
      const categoryContainer = document.getElementById(`inputs_${category}`);
      inputsByCategory[category].forEach(f => {
        const col = document.createElement('div');
        col.className = 'col-md-6 col-lg-4';
        col.innerHTML = `
          <label class="form-label">${f.label} <span class="text-warning">*</span></label>
          <input type="number" step="any" class="form-control ratio-input" name="${f.k}" id="input_${f.k}" placeholder="Enter ${f.label}" required>
          <div class="form-text text-muted small">Required for calculation</div>
        `;
        categoryContainer.appendChild(col);
      });
    });

    // Store selected keys for calculation
    inputsContainer.dataset.selected = JSON.stringify(selectedKeys);
  }

  /* Calculate button handler */
  document.getElementById('calcBtn').addEventListener('click', function(){
    const inputsContainer = document.getElementById('inputsContainer');
    const selected = JSON.parse(inputsContainer.dataset.selected || '[]');
    
    // Gather values
    const values = {};
    let valid = true;
    document.querySelectorAll('.ratio-input').forEach(inp=>{
      const val = inp.value.trim();
      values[inp.name] = val === '' ? null : parseFloat(val);
    });

    // Basic validation
    const missing = [];
    selected.forEach(key=>{
      RATIOS[key].inputs.forEach(inp=>{
        if(values[inp.k] === null || isNaN(values[inp.k])) {
          missing.push(inp.label);
        }
      });
    });
    
    if(missing.length > 0){
      const uniqueMissing = [...new Set(missing)];
      alert('Please fill required inputs:\n\n' + uniqueMissing.join('\n'));
      return;
    }

    // Compute results grouped by category
    const resultsByCategory = {};
    selected.forEach(key=>{
      const r = RATIOS[key];
      const raw = r.formula(values);
      const formatted = (r.formatVal ? r.formatVal(raw) : formatNumber(raw));
      const status = r.thresholds(raw)[0];
      const actions = r.action(raw);
      
      const category = r.category;
      if (!resultsByCategory[category]) {
        resultsByCategory[category] = [];
      }
      
      resultsByCategory[category].push({
        key, 
        label: r.label, 
        raw, 
        formatted, 
        status, 
        actions,
        description: r.description
      });
    });

    // Render into modal and show immediately
    renderResultsModal(resultsByCategory, values, selected);
  });

  /* Render results into modal */
  function renderResultsModal(resultsByCategory, inputValues, selectedKeys){
    const c = document.getElementById('resultsContainer');
    c.innerHTML = '';

    // Header block
    const ts = new Date().toLocaleString();
    const header = document.createElement('div');
    header.className = 'mb-4';
    header.innerHTML = `
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h4 class="mb-1" style="color:#0b1224;">Comprehensive Financial Ratios Report</h4>
          <div class="small-muted">Generated: ${ts}</div>
          <div class="small-muted">Ratios Analyzed: ${selectedKeys.length} across ${Object.keys(resultsByCategory).length} categories</div>
        </div>
        <div class="text-end">
          <div class="small-muted"><strong>OGMBC CONSULTANTS</strong></div>
          <div class="small-muted">Office No. A07, The Regal Tower, Business Bay, Dubai</div>
          <div class="small-muted">Contact: info@ogmbc.ae | Tel: +971509860136</div>
        </div>
      </div>
      <hr>
    `;
    c.appendChild(header);

    // Executive Summary
    const summary = document.createElement('div');
    summary.className = 'mb-4 p-3 rounded-3';
    summary.style.backgroundColor = '#f8f9fa';
    summary.style.border = '1px solid #dee2e6';
    
    let totalRatios = 0;
    let goodCount = 0;
    let warnCount = 0;
    let riskCount = 0;
    
    Object.keys(resultsByCategory).forEach(category => {
      resultsByCategory[category].forEach(r => {
        totalRatios++;
        if (r.status === 'good') goodCount++;
        else if (r.status === 'warn') warnCount++;
        else if (r.status === 'risk') riskCount++;
      });
    });
    
    summary.innerHTML = `
      <h5 class="mb-2" style="color:#0b1224;"><i class="bi bi-clipboard-data me-2"></i>Executive Summary</h5>
      <div class="row">
        <div class="col-md-3 text-center">
          <div class="display-6 text-success">${goodCount}</div>
          <div class="small-muted">Healthy Ratios</div>
        </div>
        <div class="col-md-3 text-center">
          <div class="display-6 text-warning">${warnCount}</div>
          <div class="small-muted">Need Attention</div>
        </div>
        <div class="col-md-3 text-center">
          <div class="display-6 text-danger">${riskCount}</div>
          <div class="small-muted">Require Action</div>
        </div>
        <div class="col-md-3 text-center">
          <div class="display-6 text-primary">${totalRatios}</div>
          <div class="small-muted">Total Analyzed</div>
        </div>
      </div>
    `;
    c.appendChild(summary);

    // Inputs Summary (collapsible)
    const inputsSection = document.createElement('div');
    inputsSection.className = 'mb-4';
    inputsSection.innerHTML = `
      <h5 class="mb-3" style="color:#0b1224; cursor:pointer;" onclick="toggleInputsSummary()">
        <i class="bi bi-chevron-down me-2"></i>Input Data Summary
      </h5>
      <div id="inputsSummary" class="collapse show">
        <div class="row g-2">
          ${Object.keys(inputValues).map(k => `
            <div class="col-6 col-md-4">
              <div class="small-muted">${k.replace(/_/g,' ').toUpperCase()}:</div>
              <div><strong>${formatNumber(inputValues[k])}</strong></div>
            </div>
          `).join('')}
        </div>
      </div>
    `;
    c.appendChild(inputsSection);

    // Results by Category
    Object.keys(resultsByCategory).forEach(category => {
      const categoryName = category.charAt(0).toUpperCase() + category.slice(1);
      const categorySection = document.createElement('div');
      categorySection.className = 'category-results mb-4';
      categorySection.innerHTML = `
        <h5 class="mb-3" style="color:var(--gold); border-bottom:2px solid var(--gold); padding-bottom:5px;">
          <i class="bi bi-folder-fill me-2"></i>${categoryName} Ratios
        </h5>
        <div class="row g-3" id="results_${category}"></div>
      `;
      c.appendChild(categorySection);
      
      const resultsGrid = document.getElementById(`results_${category}`);
      resultsByCategory[category].forEach(r => {
        const col = document.createElement('div');
        col.className = 'col-md-6 col-lg-4';
        col.innerHTML = `
          <div class="p-3 rounded-3 h-100" style="background:#fff; border:1px solid #dee2e6;">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <div class="fw-semibold mb-1" style="color:#0b1224;">${r.label}</div>
                <div class="small-muted" style="font-size:0.8rem;">${r.description}</div>
              </div>
              <div class="text-end">
                ${severityBadge(r.status)}
              </div>
            </div>
            <div class="mb-3">
              <div class="display-6" style="color:#0b1224;">${r.formatted}</div>
            </div>
            <hr>
            <div>
              <strong>Recommended Actions:</strong>
              <ul class="mb-0 mt-2" style="color:#0f172a; font-size:0.85rem;">
                ${r.actions.map(a=>`<li>${a}</li>`).join('')}
              </ul>
            </div>
          </div>
        `;
        resultsGrid.appendChild(col);
      });
    });

    // Overall Recommendations
    const recommendations = document.createElement('div');
    recommendations.className = 'mt-4 p-3 rounded-3';
    recommendations.style.backgroundColor = '#fff3cd';
    recommendations.style.border = '1px solid #ffc107';
    
    let priorityActions = [];
    Object.keys(resultsByCategory).forEach(category => {
      resultsByCategory[category].forEach(r => {
        if (r.status === 'risk') {
          priorityActions = priorityActions.concat(r.actions.slice(0, 2));
        }
      });
    });
    
    if (priorityActions.length === 0) {
      priorityActions = ["Maintain current financial practices and monitor ratios regularly."];
    }
    
    recommendations.innerHTML = `
      <h5 class="mb-2" style="color:#856404;"><i class="bi bi-exclamation-triangle me-2"></i>Priority Actions</h5>
      <ul class="mb-0">
        ${[...new Set(priorityActions)].slice(0, 5).map(action => `<li>${action}</li>`).join('')}
      </ul>
    `;
    c.appendChild(recommendations);

    // Attach handlers for download and save
    document.getElementById('downloadPdfBtn').onclick = () => downloadPdf(resultsByCategory, inputValues, selectedKeys);
    document.getElementById('saveResultBtn').onclick = () => saveResult(resultsByCategory, inputValues, selectedKeys);

    // Show modal
    const modalEl = document.getElementById('resultsModal');
    const modal = new bootstrap.Modal(modalEl, {backdrop:'static'});
    modal.show();

    // Store last results for potential PDF / save
    window.lastResults = {
      timestamp: new Date().toISOString(), 
      resultsByCategory, 
      inputs: inputValues,
      selectedKeys
    };
  }

  // Toggle inputs summary
  window.toggleInputsSummary = function() {
    const inputsSummary = document.getElementById('inputsSummary');
    const icon = inputsSummary.previousElementSibling.querySelector('i');
    
    if (inputsSummary.classList.contains('show')) {
      inputsSummary.classList.remove('show');
      icon.className = 'bi bi-chevron-right me-2';
    } else {
      inputsSummary.classList.add('show');
      icon.className = 'bi bi-chevron-down me-2';
    }
  };

  /* Generate PDF */
  async function downloadPdf(resultsByCategory, inputs, selectedKeys) {
    try {
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
      });

      // Letterhead background
      pdf.setFillColor(15, 40, 75);
      pdf.rect(0, 0, 210, 40, 'F');

      // Try loading the logo image for header (async-safe within async function)
      let headerLogoData = null;
      try {
        headerLogoData = await new Promise((resolve, reject) => {
          const img = new Image();
          img.crossOrigin = 'Anonymous';
          img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            resolve({ dataUrl: canvas.toDataURL('image/png'), w: img.naturalWidth, h: img.naturalHeight });
          };
          img.onerror = () => reject(new Error('Logo load failed'));
          img.src = 'resources/img/logo.png';
        });
      } catch (e) {
        console.warn('Header logo not loaded for PDF:', e);
        headerLogoData = null;
      }

      // Add header logo (if available) centered; always render the firm name below it
      if (headerLogoData) {
        const headerTargetH = 14; // mm (slightly larger)
        const aspect = headerLogoData.w / headerLogoData.h;
        const headerTargetW = headerTargetH * aspect;
        const x = (210 - headerTargetW) / 2;
        const logoY = 6;
        pdf.addImage(headerLogoData.dataUrl, 'PNG', x, logoY, headerTargetW, headerTargetH);

        // Compute company name Y position with a small margin below the logo
        const companyNameY = logoY + headerTargetH + 6; // 6mm gap
        pdf.setTextColor(255, 255, 255);
        pdf.setFontSize(18);
        pdf.setFont('helvetica', 'bold');
        pdf.text('OGM BUSINESS CONSULTANTS', 105, companyNameY, { align: 'center' });
      } else {
        pdf.setTextColor(255, 255, 255);
        pdf.setFontSize(18);
        pdf.setFont('helvetica', 'bold');
        pdf.text('OGM BUSINESS CONSULTANTS', 105, 22, { align: 'center' });
      }

      // Letterhead subtitle / contact
      pdf.setFontSize(10);
      pdf.setFont('helvetica', 'normal');
      pdf.text('Financial Advisory & Business Consulting', 105, 30, { align: 'center' });

      pdf.setTextColor(200, 200, 200);
      pdf.setFontSize(8);
      pdf.text('Office No. A07, 18th Floor, The Regal Tower, Business Bay, Dubai UAE', 105, 34, { align: 'center' });
      pdf.text('Tel: +971509860136 | Email: info@ogmbc.ae', 105, 38, { align: 'center' });

      // Report title
      pdf.setTextColor(0, 0, 0);
      pdf.setFontSize(16);
      pdf.setFont('helvetica', 'bold');
      pdf.text('FINANCIAL RATIOS ANALYSIS REPORT', 105, 60, { align: 'center' });

      // Date and reference
      pdf.setFontSize(10);
      pdf.setFont('helvetica', 'normal');
      pdf.text(`Generated: ${new Date().toLocaleDateString()}`, 20, 70);
      pdf.text(`Reference: R${Date.now().toString().slice(-6)}`, 20, 75);

      // Summary statistics
      let yPos = 85;
      pdf.setFontSize(12);
      pdf.setFont('helvetica', 'bold');
      pdf.text('EXECUTIVE SUMMARY', 20, yPos);
      
      yPos += 10;
      pdf.setFontSize(10);
      pdf.setFont('helvetica', 'normal');
      
      let totalRatios = 0;
      let goodCount = 0;
      let warnCount = 0;
      let riskCount = 0;
      
      Object.keys(resultsByCategory).forEach(category => {
        resultsByCategory[category].forEach(r => {
          totalRatios++;
          if (r.status === 'good') goodCount++;
          else if (r.status === 'warn') warnCount++;
          else if (r.status === 'risk') riskCount++;
        });
      });
      
      pdf.text(`Total Ratios Analyzed: ${totalRatios} across ${Object.keys(resultsByCategory).length} categories`, 25, yPos);
      yPos += 6;
      pdf.text(`• ${goodCount} ratios indicate healthy financial position`, 25, yPos);
      yPos += 6;
      pdf.text(`• ${warnCount} ratios require monitoring and improvement`, 25, yPos);
      yPos += 6;
      pdf.text(`• ${riskCount} ratios need immediate attention`, 25, yPos);
      yPos += 10;

      // Category-wise results
      Object.keys(resultsByCategory).forEach(category => {
        if (yPos > 250) {
          pdf.addPage();
          yPos = 40;
        }
        
        const categoryName = category.charAt(0).toUpperCase() + category.slice(1);
        pdf.setFontSize(12);
        pdf.setFont('helvetica', 'bold');
        pdf.text(`${categoryName.toUpperCase()} RATIOS`, 20, yPos);
        yPos += 8;
        
        // Table headers
        pdf.setFillColor(241, 191, 112);
        pdf.rect(20, yPos, 170, 6, 'F');
        
        pdf.setTextColor(0, 0, 0);
        pdf.setFontSize(8);
        pdf.setFont('helvetica', 'bold');
        pdf.text('RATIO', 25, yPos + 4);
        pdf.text('VALUE', 90, yPos + 4);
        pdf.text('STATUS', 130, yPos + 4);
        pdf.text('ACTION', 150, yPos + 4);
        
        yPos += 8;
        
        resultsByCategory[category].forEach((result, index) => {
          if (yPos > 270) {
            pdf.addPage();
            yPos = 40;
            pdf.setFillColor(241, 191, 112);
            pdf.rect(20, yPos - 8, 170, 6, 'F');
            pdf.setTextColor(0, 0, 0);
            pdf.setFontSize(8);
            pdf.setFont('helvetica', 'bold');
            pdf.text('RATIO', 25, yPos - 4);
            pdf.text('VALUE', 90, yPos - 4);
            pdf.text('STATUS', 130, yPos - 4);
            pdf.text('ACTION', 150, yPos - 4);
          }
          
          // Alternate row background
          if (index % 2 === 0) {
            pdf.setFillColor(245, 245, 245);
            pdf.rect(20, yPos - 2, 170, 6, 'F');
          }
          
          pdf.setTextColor(0, 0, 0);
          pdf.setFontSize(7);
          pdf.setFont('helvetica', 'normal');
          
          // Ratio name
          const ratioName = result.label.length > 25 ? result.label.substring(0, 25) + '...' : result.label;
          pdf.text(ratioName, 25, yPos + 2);
          
          // Value
          pdf.text(result.formatted, 90, yPos + 2);
          
          // Status
          if (result.status === 'good') pdf.setTextColor(0, 128, 0);
          else if (result.status === 'warn') pdf.setTextColor(255, 165, 0);
          else if (result.status === 'risk') pdf.setTextColor(255, 0, 0);
          
          pdf.text(result.status.toUpperCase(), 130, yPos + 2);
          
          // Reset color
          pdf.setTextColor(0, 0, 0);
          const action = result.actions[0].length > 30 ? result.actions[0].substring(0, 30) + '...' : result.actions[0];
          pdf.text(action, 150, yPos + 2);
          
          yPos += 8;
        });
        
        yPos += 10;
      });

      // Footer
      pdf.setFontSize(8);
      pdf.setTextColor(128, 128, 128);
      pdf.text('Confidential - For Client Use Only', 105, 285, { align: 'center' });
      pdf.text('This report was generated by OGMBC Financial Analysis System', 105, 290, { align: 'center' });
      pdf.text('Contact: info@ogmbc.ae | Tel: +971509860136', 105, 295, { align: 'center' });

      // Create watermark using the logo (no tilt) if available, otherwise use text
      let watermarkDataUrl = null;
      let watermarkCanvasW = 1600;
      let watermarkCanvasH = 600;
      try {
        if (headerLogoData && headerLogoData.dataUrl) {
          const img = new Image();
          img.src = headerLogoData.dataUrl;
          await new Promise((resolve, reject) => {
            img.onload = resolve;
            img.onerror = () => reject(new Error('Logo data image failed'));
          });

          // Larger canvas for a big watermark (4x larger visual impact)
          const canvas = document.createElement('canvas');
          canvas.width = 3200; // increased size
          canvas.height = 1200;
          watermarkCanvasW = canvas.width;
          watermarkCanvasH = canvas.height;
          const ctx = canvas.getContext('2d');
          ctx.clearRect(0, 0, canvas.width, canvas.height);
          ctx.globalAlpha = 0.18; // slightly more visible

          // Draw the logo centered and scaled to occupy most of canvas height
          const targetH = canvas.height * 0.85;
          const aspect = img.naturalWidth / img.naturalHeight;
          const targetW = targetH * aspect;
          ctx.drawImage(img, (canvas.width - targetW) / 2, (canvas.height - targetH) / 2, targetW, targetH);

          watermarkDataUrl = canvas.toDataURL('image/png');
        } else {
          // Fallback: plain centered text watermark (no rotation)
          const canvas = document.createElement('canvas');
          canvas.width = 2400;
          canvas.height = 600;
          watermarkCanvasW = canvas.width;
          watermarkCanvasH = canvas.height;
          const ctx = canvas.getContext('2d');
          ctx.clearRect(0, 0, canvas.width, canvas.height);
          ctx.globalAlpha = 0.18;
          ctx.fillStyle = '#111';
          ctx.font = 'bold 80px Arial';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.fillText('FINANCIAL RATIOS ANALYSIS REPORT', canvas.width / 2, canvas.height / 2);
          watermarkDataUrl = canvas.toDataURL('image/png');
        }
      } catch (e) {
        console.warn('Could not create watermark image:', e);
        watermarkDataUrl = null;
      }

      // Apply watermark to every page (centered) using actual image aspect ratio
      try {
        const pageCount = pdf.getNumberOfPages();
        for (let p = 1; p <= pageCount; p++) {
          pdf.setPage(p);
          if (watermarkDataUrl) {
            // load watermark image to get real aspect
            const wmImg = new Image();
            await new Promise((resolve, reject) => {
              wmImg.onload = resolve;
              wmImg.onerror = () => reject(new Error('Watermark load failed'));
              wmImg.src = watermarkDataUrl;
            });

            const pageW = pdf.internal.pageSize.getWidth();
            const pageH = pdf.internal.pageSize.getHeight();

            // Make watermark up to 4x bigger than the previous base size, capped to page dimensions
            const baseW = pageW * 0.95; // previous base width
            const desiredW = Math.min(pageW * 0.99, baseW * 4); // 4x but not exceed page width
            const aspect = wmImg.naturalWidth / wmImg.naturalHeight;
            let wmWidth = desiredW;
            let wmHeight = wmWidth / aspect;

            // If height exceeds page, downscale to fit within page height
            if (wmHeight > pageH * 0.98) {
              const scale = (pageH * 0.98) / wmHeight;
              wmWidth = wmWidth * scale;
              wmHeight = wmHeight * scale;
            }

            const wmX = (pageW - wmWidth) / 2;
            const wmY = (pageH - wmHeight) / 2;

            pdf.addImage(watermarkDataUrl, 'PNG', wmX, wmY, wmWidth, wmHeight);
          }
        }
      } catch (e) {
        console.warn('Could not apply watermark to pages:', e);
      }

      // Save the PDF
      const fileName = `OGMBC_Financial_Analysis_${new Date().toISOString().slice(0,10)}.pdf`;
      pdf.save(fileName);

    } catch (error) {
      console.error('Error generating PDF:', error);
      alert('Error generating PDF. Please try again or contact support.');
    }
  }

  /* Save results to localStorage */
  function saveResult(resultsByCategory, inputs, selectedKeys){
    const store = JSON.parse(localStorage.getItem('ogmbc_saved_results') || '[]');
    const entry = {
      id: Date.now(), 
      ts: new Date().toISOString(), 
      inputs, 
      resultsByCategory,
      selectedKeys
    };
    store.push(entry);
    localStorage.setItem('ogmbc_saved_results', JSON.stringify(store));
    renderSaved();
    
    // Show success notification
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alert.style.top = '20px';
    alert.style.right = '20px';
    alert.style.zIndex = '99999';
    alert.innerHTML = `
      <i class="bi bi-check-circle me-2"></i>Result saved successfully!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
  }

  /* Render saved results list */
  function renderSaved(){
    const area = document.getElementById('savedArea');
    const store = JSON.parse(localStorage.getItem('ogmbc_saved_results') || '[]');
    if(store.length === 0){
      area.innerHTML = '<div class="small-muted">No saved results yet.</div>';
      return;
    }
    
    let html = `<div class="table-responsive"><table class="table table-sm saved-table bg-white rounded-2"><thead><tr><th>Date & Time</th><th>Categories</th><th>Ratios</th><th>Actions</th></tr></thead><tbody>`;
    
    store.slice().reverse().forEach(s => {
      const date = new Date(s.ts);
      const categories = Object.keys(s.resultsByCategory || {}).length;
      const ratios = s.selectedKeys ? s.selectedKeys.length : 0;
      
      html += `<tr>
        <td>${date.toLocaleDateString()} ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</td>
        <td>${categories} categories</td>
        <td>${ratios} ratios</td>
        <td>
          <button class="btn btn-sm btn-outline-primary" onclick='viewSaved(${s.id})'>
            <i class="bi bi-eye me-1"></i>View
          </button>
          <button class="btn btn-sm btn-outline-danger" onclick='confirmDelete(${s.id})'>
            <i class="bi bi-trash me-1"></i>Delete
          </button>
        </td>
      </tr>`;
    });
    
    html += `</tbody></table></div>`;
    area.innerHTML = html;
  }

  /* View saved result */
  function viewSaved(id){
    const store = JSON.parse(localStorage.getItem('ogmbc_saved_results') || '[]');
    const entry = store.find(s => s.id === id);
    if(!entry) return;
    renderResultsModal(entry.resultsByCategory, entry.inputs, entry.selectedKeys || []);
  }

  /* Delete saved flow */
  let pendingDeleteId = null;
  function confirmDelete(id){
    pendingDeleteId = id;
    const md = new bootstrap.Modal(document.getElementById('confirmDeleteSaved'));
    md.show();
  }
  
  document.getElementById('confirmDeleteYes').addEventListener('click', function(){
    const store = JSON.parse(localStorage.getItem('ogmbc_saved_results') || '[]');
    const filtered = store.filter(s => s.id !== pendingDeleteId);
    localStorage.setItem('ogmbc_saved_results', JSON.stringify(filtered));
    renderSaved();
    pendingDeleteId = null;
    bootstrap.Modal.getInstance(document.getElementById('confirmDeleteSaved')).hide();
  });

  /* Clear saved */
  document.getElementById('clearSaved').addEventListener('click', function(){
    if(!confirm('Clear all saved results? This action cannot be undone.')) return;
    localStorage.removeItem('ogmbc_saved_results');
    renderSaved();
  });

  /* Initialize */
  document.addEventListener('DOMContentLoaded', function() {
    renderRatioOptionsByCategory();
    renderSaved();
    
    // Initialize tab functionality
    const tabTriggers = document.querySelectorAll('#categoryTabs button');
    tabTriggers.forEach(trigger => {
      trigger.addEventListener('shown.bs.tab', function() {
        // Update any necessary state when tab changes
      });
    });
  });

  /* Ensure modal z-index */
  document.getElementById('resultsModal').addEventListener('shown.bs.modal', function () {
    const modalEl = this;
    modalEl.style.zIndex = 99999;
    document.querySelectorAll('.modal-backdrop').forEach(b => b.style.zIndex = 99998);
  });
</script>