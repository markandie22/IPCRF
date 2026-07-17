<?php
include("db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Access denied.");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ipcrf'])) {
    $objective = trim($_POST['objective'] ?? '');
    $performanceIndicator = trim($_POST['performance_indicator'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');

    if ($objective === '' || $performanceIndicator === '' || $rating < 1 || $rating > 5) {
        $message = '<div class="message">Please complete all required fields with a valid rating (1-5).</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO ipcrf_entries (user_id, objective, performance_indicator, rating, remarks) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $_SESSION['user_id'], $objective, $performanceIndicator, $rating, $remarks);

        if ($stmt->execute()) {
            $message = '<div class="message message--success">IPCRF entry submitted successfully.</div>';
        } else {
            $message = '<div class="message">Failed to submit IPCRF entry.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>IPCRF Form Wizard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wizard-wrapper">
    <div class="wizard-tabs">
        <button type="button" class="wizard-tab active" data-step="1">Step 1</button>
        <button type="button" class="wizard-tab" data-step="2">Step 2</button>
        <button type="button" class="wizard-tab" data-step="3">Step 3</button>
        <button type="button" class="wizard-tab" data-step="4">Step 4</button>
        <button type="button" class="wizard-tab" data-step="5">Step 5</button>
    </div>

    <form method="POST" id="ipcrfWizardForm">
        <section class="wizard-step active" data-step="1">
            <div class="intro-board">
                <h1>OFFICIAL ELECTRONIC IPCRF TOOL</h1>
                <h2>SY 2025-2026</h2>
                <p>
                    This is the official tool that will be used by DepEd teachers in the preparation of their
                    Individual Performance Commitment and Review Forms.
                </p>
                <p>
                    Select a career stage to view the objectives set for this tool. Click Start to proceed.
                </p>

                <div class="intro-grid">
                    <label for="school_year">School Year:</label>
                    <input type="text" id="school_year" name="school_year" value="2025-2026" required>

                    <label for="career_stage">Select Career Stage:</label>
                    <select id="career_stage" name="career_stage" required>
                        <option value="">-- Select Career Stage --</option>
                        <option value="Beginning towards Proficient">Beginning towards Proficient</option>
                        <option value="Proficient">Proficient</option>
                        <option value="Highly Proficient">Highly Proficient</option>
                        <option value="Distinguished">Distinguished</option>
                    </select>
                </div>

                <div class="wizard-actions">
                    <button type="button" class="btn-next" data-next="2">START</button>
                </div>
            </div>
        </section>

        <section class="wizard-step" data-step="2">
            <div class="login-card form-card">
                <h2>Step 2: IPCRF Details</h2>

                <label for="objective">Objective</label>
                <textarea id="objective" name="objective" rows="4" required></textarea>

                <label for="performance_indicator">Performance Indicator</label>
                <textarea id="performance_indicator" name="performance_indicator" rows="4" required></textarea>

                <label for="rating">Rating (1-5)</label>
                <select id="rating" name="rating" required>
                    <option value="">-- Select Rating --</option>
                    <option value="1">1 - Needs Improvement</option>
                    <option value="2">2 - Fair</option>
                    <option value="3">3 - Satisfactory</option>
                    <option value="4">4 - Very Satisfactory</option>
                    <option value="5">5 - Outstanding</option>
                </select>

                <label for="remarks">Remarks</label>
                <textarea id="remarks" name="remarks" rows="3"></textarea>

                <div class="wizard-actions split">
                    <button type="button" class="btn-back" data-back="1">Back</button>
                    <button type="button" class="btn-next" data-next="3">Next</button>
                </div>
            </div>
        </section>

        <section class="wizard-step" data-step="3">
            <div class="intro-board demographic-board">
                <h2>Step 3: Demographic Profile</h2>

                <div class="demo-grid demo-grid-3">
                    <label for="region">Region</label>
                    <input type="text" id="region" name="region">

                    <label for="division">Division</label>
                    <input type="text" id="division" name="division">

                    <label for="school_id">School ID</label>
                    <input type="text" id="school_id" name="school_id">

                    <label for="school_name">CLC / School Name</label>
                    <input type="text" id="school_name" name="school_name">

                    <label for="school_type">School Type</label>
                    <input type="text" id="school_type" name="school_type">

                    <label for="school_size">School Size</label>
                    <input type="text" id="school_size" name="school_size">

                    <label for="curricular_classification">Curricular Classification</label>
                    <input type="text" id="curricular_classification" name="curricular_classification">

                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name">

                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name">

                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name">

                    <label for="employee_id">Employee ID</label>
                    <input type="text" id="employee_id" name="employee_id">

                    <label for="position">Position</label>
                    <input type="text" id="position" name="position">

                    <label for="employment_status">Employment Status</label>
                    <input type="text" id="employment_status" name="employment_status">

                    <label for="age">Age</label>
                    <input type="text" id="age" name="age">

                    <label for="sex">Sex</label>
                    <input type="text" id="sex" name="sex">

                    <label for="years_teaching">Number of Years in Teaching</label>
                    <input type="text" id="years_teaching" name="years_teaching">

                    <label for="highest_degree">Highest Degree Obtained</label>
                    <input type="text" id="highest_degree" name="highest_degree">

                    <label for="level_taught">Level Taught</label>
                    <input type="text" id="level_taught" name="level_taught">

                    <label for="deped_email">DepEd Email Address</label>
                    <input type="email" id="deped_email" name="deped_email">

                    <label for="tin">TIN</label>
                    <input type="text" id="tin" name="tin">
                </div>

                <div class="checkbox-panels">
                    <div class="checkbox-panel">
                        <h3>AREA(S) OF SPECIALIZATION</h3>
                        <div class="checkbox-grid">
                            <label><input type="checkbox" name="specialization[]" value="English"> English</label>
                            <label><input type="checkbox" name="specialization[]" value="Values Education"> Values Education</label>
                            <label><input type="checkbox" name="specialization[]" value="Filipino"> Filipino</label>
                            <label><input type="checkbox" name="specialization[]" value="SPED"> SPED</label>
                            <label><input type="checkbox" name="specialization[]" value="Mathematics"> Mathematics</label>
                            <label><input type="checkbox" name="specialization[]" value="Music"> Music</label>
                            <label><input type="checkbox" name="specialization[]" value="General Science"> General Science</label>
                            <label><input type="checkbox" name="specialization[]" value="Arts"> Arts</label>
                            <label><input type="checkbox" name="specialization[]" value="Biology"> Biology</label>
                            <label><input type="checkbox" name="specialization[]" value="Physical Education"> Physical Education</label>
                            <label><input type="checkbox" name="specialization[]" value="Chemistry"> Chemistry</label>
                            <label><input type="checkbox" name="specialization[]" value="Health"> Health</label>
                            <label><input type="checkbox" name="specialization[]" value="Physics"> Physics</label>
                            <label><input type="checkbox" name="specialization[]" value="TLE/HE/TVL"> TLE/HE/TVL</label>
                            <label><input type="checkbox" name="specialization[]" value="Social Science"> Social Science</label>
                            <label><input type="checkbox" name="specialization[]" value="Early Childhood Education"> Early Childhood Education</label>
                        </div>
                        <label for="specialization_others">Others (specify)</label>
                        <input type="text" id="specialization_others" name="specialization_others">
                    </div>

                    <div class="checkbox-panel">
                        <h3>SUBJECT(S) TAUGHT</h3>
                        <div class="checkbox-grid">
                            <label><input type="checkbox" name="subjects_taught[]" value="All Subjects"> All Subjects</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="PE and Health"> PE and Health</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="MTB-MLE"> MTB-MLE</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="Makabansa"> Makabansa</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="Madrasah ALIVE"> Madrasah ALIVE</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="Languages / Reading and Literacy"> Languages / Reading and Literacy</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="Filipino"> Filipino</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="TLE/EPP-HE"> TLE/EPP-HE</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="English"> English</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="TLE/EPP-LE/Tech-Voc"> TLE/EPP-LE/Tech-Voc</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="Mathematics"> Mathematics</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="ALS"> ALS</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="Science / Physical and Natural Environment"> Science / Physical and Natural Environment</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="IPED"> IPED</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="Araling Panlipunan"> Araling Panlipunan</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="Special Programs"> Special Programs</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="GMRC / EsP"> GMRC / EsP</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="MAPEH"> MAPEH</label>
                            <label><input type="checkbox" name="subjects_taught[]" value="Music and Arts"> Music and Arts</label>
                        </div>
                        <label for="subjects_others">Others (specify)</label>
                        <input type="text" id="subjects_others" name="subjects_others">
                    </div>
                </div>

                <div class="wizard-actions split">
                    <button type="button" class="btn-back" data-back="2">Back</button>
                    <button type="button" class="btn-next" data-next="4">Next</button>
                </div>
            </div>
        </section>

        <section class="wizard-step" data-step="4">
            <div class="login-card form-card">
                <h2>Step 4: Review & Submit</h2>
                <p>Review your details, then submit your IPCRF entry.</p>

                <div class="review-box">
                    <p><strong>School Year:</strong> <span id="review_school_year"></span></p>
                    <p><strong>Career Stage:</strong> <span id="review_career_stage"></span></p>
                    <p><strong>Objective:</strong> <span id="review_objective"></span></p>
                    <p><strong>Performance Indicator:</strong> <span id="review_performance_indicator"></span></p>
                    <p><strong>Rating:</strong> <span id="review_rating"></span></p>
                    <p><strong>Remarks:</strong> <span id="review_remarks"></span></p>
                    <p><strong>Teacher Name:</strong> <span id="review_teacher_name"></span></p>
                    <p><strong>Region / Division:</strong> <span id="review_region_division"></span></p>
                    <p><strong>School:</strong> <span id="review_school_name"></span></p>
                    <p><strong>Position:</strong> <span id="review_position"></span></p>
                    <p><strong>Years in Teaching:</strong> <span id="review_years_teaching"></span></p>
                </div>

                <div class="wizard-actions split">
                    <button type="button" class="btn-back" data-back="3">Back</button>
                    <button type="button" class="btn-next" data-next="5">Next</button>
                </div>

                <?php echo $message; ?>
            </div>
        </section>

        <section class="wizard-step" data-step="5">
            <div class="step5-wrap">
                <h2>Step 5: IPCRF Rating Sheet</h2>

                <div class="step5-table-scroll">
                    <table class="step5-table">
                        <tr>
                            <th colspan="3">Name of Employee:</th>
                            <td colspan="4"><input type="text" name="s5_employee_name"></td>
                            <th>RATER Last Name:</th>
                            <td><input type="text" name="s5_rater_last"></td>
                            <th>* First:</th>
                            <td><input type="text" name="s5_rater_first"></td>
                            <th>Middle:</th>
                            <td><input type="text" name="s5_rater_middle"></td>
                        </tr>
                        <tr>
                            <th colspan="3">Position:</th>
                            <td colspan="4"><input type="text" name="s5_employee_position"></td>
                            <th>* Position:</th>
                            <td><input type="text" name="s5_rater_position"></td>
                            <th>* Email:</th>
                            <td colspan="3"><input type="email" name="s5_rater_email"></td>
                        </tr>
                        <tr>
                            <th colspan="3">Bureau/Center/Service/Division:</th>
                            <td colspan="4"><input type="text" name="s5_bureau"></td>
                            <th>* Date of Review:</th>
                            <td><input type="text" name="s5_date_review"></td>
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <th colspan="3">Rating Period:</th>
                            <td colspan="11"><input type="text" name="s5_rating_period"></td>
                        </tr>

                        <tr>
                            <th rowspan="2">KRA</th>
                            <th rowspan="2">Objectives</th>
                            <th rowspan="2">PPST</th>
                            <th rowspan="2">COI/NCOI</th>
                            <th rowspan="2">Weight per Objective</th>
                            <th rowspan="2">COT Indicator No.</th>
                            <th colspan="2">COT 1</th>
                            <th colspan="2">COT 2</th>
                            <th colspan="2">COT 3</th>
                            <th colspan="2">COT 4</th>
                            <th rowspan="2">Ave</th>
                            <th colspan="4">IPCRF Numerical Ratings</th>
                            <th rowspan="2">Score</th>
                            <th rowspan="2">Adjectival Rating</th>
                        </tr>
                        <tr>
                            <th>Rating</th>
                            <th>RPMS 5-pt Scale</th>
                            <th>Rating</th>
                            <th>RPMS 5-pt Scale</th>
                            <th>Rating</th>
                            <th>RPMS 5-pt Scale</th>
                            <th>Rating</th>
                            <th>RPMS 5-pt Scale</th>
                            <th>Q</th>
                            <th>E</th>
                            <th>T</th>
                            <th>Ave</th>
                        </tr>

                        <tr>
                            <td><input type="text" name="s5_kra_1"></td>
                            <td><input type="text" name="s5_obj_1"></td>
                            <td><input type="text" name="s5_ppst_1"></td>
                            <td><input type="text" name="s5_coincoi_1"></td>
                            <td><input type="text" name="s5_weight_1"></td>
                            <td><input type="text" name="s5_cot_no_1"></td>
                            <td><input type="text" name="s5_c1_rating_1"></td>
                            <td><input type="text" name="s5_c1_scale_1"></td>
                            <td><input type="text" name="s5_c2_rating_1"></td>
                            <td><input type="text" name="s5_c2_scale_1"></td>
                            <td><input type="text" name="s5_c3_rating_1"></td>
                            <td><input type="text" name="s5_c3_scale_1"></td>
                            <td><input type="text" name="s5_c4_rating_1"></td>
                            <td><input type="text" name="s5_c4_scale_1"></td>
                            <td><input type="text" name="s5_ave_1"></td>
                            <td><input type="text" name="s5_q_1"></td>
                            <td><input type="text" name="s5_e_1"></td>
                            <td><input type="text" name="s5_t_1"></td>
                            <td><input type="text" name="s5_nr_ave_1"></td>
                            <td><input type="text" name="s5_score_1"></td>
                            <td><input type="text" name="s5_adj_1"></td>
                        </tr>
                        <tr>
                            <td><input type="text" name="s5_kra_2"></td>
                            <td><input type="text" name="s5_obj_2"></td>
                            <td><input type="text" name="s5_ppst_2"></td>
                            <td><input type="text" name="s5_coincoi_2"></td>
                            <td><input type="text" name="s5_weight_2"></td>
                            <td><input type="text" name="s5_cot_no_2"></td>
                            <td><input type="text" name="s5_c1_rating_2"></td>
                            <td><input type="text" name="s5_c1_scale_2"></td>
                            <td><input type="text" name="s5_c2_rating_2"></td>
                            <td><input type="text" name="s5_c2_scale_2"></td>
                            <td><input type="text" name="s5_c3_rating_2"></td>
                            <td><input type="text" name="s5_c3_scale_2"></td>
                            <td><input type="text" name="s5_c4_rating_2"></td>
                            <td><input type="text" name="s5_c4_scale_2"></td>
                            <td><input type="text" name="s5_ave_2"></td>
                            <td><input type="text" name="s5_q_2"></td>
                            <td><input type="text" name="s5_e_2"></td>
                            <td><input type="text" name="s5_t_2"></td>
                            <td><input type="text" name="s5_nr_ave_2"></td>
                            <td><input type="text" name="s5_score_2"></td>
                            <td><input type="text" name="s5_adj_2"></td>
                        </tr>
                        <tr>
                            <td><input type="text" name="s5_kra_3"></td>
                            <td><input type="text" name="s5_obj_3"></td>
                            <td><input type="text" name="s5_ppst_3"></td>
                            <td><input type="text" name="s5_coincoi_3"></td>
                            <td><input type="text" name="s5_weight_3"></td>
                            <td><input type="text" name="s5_cot_no_3"></td>
                            <td><input type="text" name="s5_c1_rating_3"></td>
                            <td><input type="text" name="s5_c1_scale_3"></td>
                            <td><input type="text" name="s5_c2_rating_3"></td>
                            <td><input type="text" name="s5_c2_scale_3"></td>
                            <td><input type="text" name="s5_c3_rating_3"></td>
                            <td><input type="text" name="s5_c3_scale_3"></td>
                            <td><input type="text" name="s5_c4_rating_3"></td>
                            <td><input type="text" name="s5_c4_scale_3"></td>
                            <td><input type="text" name="s5_ave_3"></td>
                            <td><input type="text" name="s5_q_3"></td>
                            <td><input type="text" name="s5_e_3"></td>
                            <td><input type="text" name="s5_t_3"></td>
                            <td><input type="text" name="s5_nr_ave_3"></td>
                            <td><input type="text" name="s5_score_3"></td>
                            <td><input type="text" name="s5_adj_3"></td>
                        </tr>

                        <tr>
                            <th colspan="18" class="text-right">Final Rating</th>
                            <td><input type="text" name="s5_final_numeric"></td>
                            <td colspan="2"><input type="text" name="s5_final_adjectival"></td>
                        </tr>
                    </table>
                </div>

                <div class="step5-signatory-grid">
                    <div class="sign-card">
                        <label>Rater:</label>
                        <input type="text" name="s5_sign_rater_name">
                        <label>Position:</label>
                        <input type="text" name="s5_sign_rater_position">
                    </div>
                    <div class="sign-card">
                        <label>Approving Authority:</label>
                        <input type="text" name="s5_sign_approver_name">
                        <label>Position:</label>
                        <input type="text" name="s5_sign_approver_position">
                        <label>Email:</label>
                        <input type="email" name="s5_sign_approver_email">
                    </div>
                </div>

                <div class="wizard-actions split">
                    <button type="button" class="btn-back" data-back="4">Back</button>
                    <button type="submit" name="submit_ipcrf">Submit IPCRF</button>
                </div>

                <div class="wizard-actions">
                    <a class="btn link-btn" href="dashboard.php">Return to Dashboard</a>
                </div>

                <?php echo $message; ?>
            </div>
        </section>
    </form>
</div>

<script>
(function () {
    const tabs = document.querySelectorAll('.wizard-tab');
    const steps = document.querySelectorAll('.wizard-step');
    const nextButtons = document.querySelectorAll('.btn-next');
    const backButtons = document.querySelectorAll('.btn-back');

    function showStep(stepNumber) {
        tabs.forEach(tab => tab.classList.toggle('active', tab.dataset.step === String(stepNumber)));
        steps.forEach(step => step.classList.toggle('active', step.dataset.step === String(stepNumber)));

        if (String(stepNumber) === '4') {
            document.getElementById('review_school_year').textContent = document.getElementById('school_year').value || '-';
            document.getElementById('review_career_stage').textContent = document.getElementById('career_stage').value || '-';
            document.getElementById('review_objective').textContent = document.getElementById('objective').value || '-';
            document.getElementById('review_performance_indicator').textContent = document.getElementById('performance_indicator').value || '-';
            document.getElementById('review_rating').textContent = document.getElementById('rating').value || '-';
            document.getElementById('review_remarks').textContent = document.getElementById('remarks').value || '-';

            const firstName = document.getElementById('first_name').value || '';
            const middleName = document.getElementById('middle_name').value || '';
            const lastName = document.getElementById('last_name').value || '';
            const region = document.getElementById('region').value || '-';
            const division = document.getElementById('division').value || '-';

            document.getElementById('review_teacher_name').textContent = `${firstName} ${middleName} ${lastName}`.trim() || '-';
            document.getElementById('review_region_division').textContent = `${region} / ${division}`;
            document.getElementById('review_school_name').textContent = document.getElementById('school_name').value || '-';
            document.getElementById('review_position').textContent = document.getElementById('position').value || '-';
            document.getElementById('review_years_teaching').textContent = document.getElementById('years_teaching').value || '-';
        }
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            showStep(this.dataset.step);
        });
    });

    nextButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            showStep(this.dataset.next);
        });
    });

    backButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            showStep(this.dataset.back);
        });
    });
})();
</script>
</body>
</html>
