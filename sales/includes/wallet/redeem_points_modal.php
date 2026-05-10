<!-- Redeem Points Modal -->
<div class="modal fade" id="redeemPointsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cash-stack me-2"></i>Redeem Points
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="redeemPointsForm" method="POST" action="includes/wallet/submit_redemption.php">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        You can redeem points earned from <strong>Engagements and Client Feedback</strong> only.
                        Points from CDP (certificates, courses, loyalty, behavior) cannot be redeemed.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Month Points</label>
                        <div class="form-control bg-light" id="total_month_points">Loading...</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Points Already Redeemed</label>
                        <div class="form-control bg-light" id="already_redeemed">0</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Available to Redeem</label>
                        <div class="form-control bg-light fw-bold text-success" id="available_points">0</div>
                        <div class="form-text">You need at least 1,000 total points to be eligible. Points above 1,000 can be redeemed.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="points_to_redeem" class="form-label">Points to Redeem *</label>
                        <input type="number" id="points_to_redeem" name="points_to_redeem" class="form-control" min="1" required>
                        <div class="form-text" id="redemption_value"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="redemption_notes" class="form-label">Notes (Optional)</label>
                        <textarea name="notes" id="redemption_notes" class="form-control" rows="2" placeholder="Any additional notes for the admin..."></textarea>
                    </div>
                    
                    <input type="hidden" name="month" id="redeem_month" value="<?php echo date('m'); ?>">
                    <input type="hidden" name="year" id="redeem_year" value="<?php echo date('Y'); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitRedemptionBtn" disabled>
                        <i class="bi bi-cash-stack me-1"></i>Submit Redemption Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load redemption eligibility when modal opens
document.getElementById('redeemPointsModal')?.addEventListener('show.bs.modal', function() {
    fetch('includes/ajax/get_redemption_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total_month_points').innerHTML = data.total_month_points + ' points';
                document.getElementById('already_redeemed').innerHTML = data.already_redeemed + ' points';
                document.getElementById('available_points').innerHTML = data.available_points + ' points';
                
                const pointsInput = document.getElementById('points_to_redeem');
                const submitBtn = document.getElementById('submitRedemptionBtn');
                const redemptionValue = document.getElementById('redemption_value');
                
                pointsInput.max = data.available_points;
                pointsInput.placeholder = `Enter up to ${data.available_points}`;
                
                if (data.has_request) {
                    pointsInput.disabled = true;
                    submitBtn.disabled = true;
                    redemptionValue.innerHTML = `<span class="text-warning">You already have a ${data.request_status} request for ${data.requested_points} points this month.</span>`;
                } else if (data.available_points > 0) {
                    pointsInput.disabled = false;
                    submitBtn.disabled = false;
                } else {
                    pointsInput.disabled = true;
                    submitBtn.disabled = true;
                    redemptionValue.innerHTML = '<span class="text-danger">No points available for redemption this month.</span>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading redemption status:', error);
        });
});

// Update redemption value display
document.getElementById('points_to_redeem')?.addEventListener('input', function() {
    const value = this.value;
    const redemptionValue = document.getElementById('redemption_value');
    if (value > 0) {
        redemptionValue.innerHTML = `<span class="text-success">You will receive AED ${value} upon approval.</span>`;
    } else {
        redemptionValue.innerHTML = '';
    }
});

// Validate form before submission
document.getElementById('redeemPointsForm')?.addEventListener('submit', function(e) {
    const pointsInput = document.getElementById('points_to_redeem');
    const maxPoints = parseInt(pointsInput.max);
    const points = parseInt(pointsInput.value);
    
    if (points <= 0) {
        e.preventDefault();
        alert('Please enter a valid number of points to redeem.');
        return false;
    }
    
    if (points > maxPoints) {
        e.preventDefault();
        alert(`You can only redeem up to ${maxPoints} points this month.`);
        return false;
    }
    
    return true;
});
</script>