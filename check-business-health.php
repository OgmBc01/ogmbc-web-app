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
        <div class="card card-app-1 shadow-sm">
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
                        <!-- Add this right after the category tabs div -->
                        <div class="mobile-scroll-hint d-block d-md-none text-center mb-5" style="font-size: 0.75rem; color: var(--muted); margin-top: -20px;">
                            <i class="bi bi-arrow-left-right me-1"></i> Scroll horizontally to view more categories
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
                                <button id="backToStep1" type="button" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-left me-1"></i>Back to Selection
                                </button>
                                <div>
                                    <button id="calcBtn" type="button" class="btn btn-gold btn-primary btn-sm">
                                        <i class="bi bi-calculator me-1"></i>Calculate Ratios
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <hr class="border-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="mb-0"><i class="bi bi-save me-2"></i>Saved Results</h6>
                  <div><a id="clearSaved" class="btn btn-sm btn-secondary clear-saved-btn">Clear All Saved</a></div>
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