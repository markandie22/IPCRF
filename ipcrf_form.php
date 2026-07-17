<?php
include("db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Access denied.");
}

$message = "";
$jumpToLastStep = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ipcrf'])) {
    $objective = trim($_POST['objective'] ?? '');
    $performanceIndicator = trim($_POST['performance_indicator'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');
    $fullData = json_encode($_POST, JSON_UNESCAPED_UNICODE);

    if ($objective === '' || $performanceIndicator === '' || $rating < 1 || $rating > 5) {
        $message = '<div class="message">Please complete all required fields with a valid rating (1-5).</div>';
        $jumpToLastStep = true;
    } else {
        $stmt = $conn->prepare("INSERT INTO ipcrf_entries (user_id, objective, performance_indicator, rating, remarks, full_data) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississ", $_SESSION['user_id'], $objective, $performanceIndicator, $rating, $remarks, $fullData);

        if ($stmt->execute()) {
            // Post/Redirect/Get: prevents a page refresh from re-submitting
            // the form and creating a duplicate ipcrf_entries row.
            header("Location: ipcrf_form.php?submitted=1");
            exit;
        } else {
            $message = '<div class="message">Failed to submit IPCRF entry.</div>';
            $jumpToLastStep = true;
        }
    }
} elseif (($_GET['submitted'] ?? '') === '1') {
    $message = '<div class="message message--success">IPCRF entry submitted successfully.</div>';
    $jumpToLastStep = true;
}

/**
 * Reference data for the Official IPCRF Rating Sheet (Part I).
 * Text mirrors the official DepEd IPCRF rubric wording and is shown as
 * read-only reference; only Actual Results / Rating cells are fillable.
 */
function cotQuality($label = 'the objective') {
    return [
        5 => "Demonstrated Level 7 in $label as shown in COT rating sheets / inter-observer agreement forms",
        4 => "Demonstrated Level 6 in $label as shown in COT rating sheets / inter-observer agreement forms",
        3 => "Demonstrated Level 5 in $label as shown in COT rating sheets / inter-observer agreement forms",
        2 => "Demonstrated Level 4 in $label as shown in COT rating sheets / inter-observer agreement forms",
        1 => "Demonstrated Level 3 in $label as shown in COT rating sheets / inter-observer agreement forms, or No acceptable evidence was shown",
    ];
}
$cotEfficiency = [
    5 => 'Objective was met within the allotted time',
    4 => '',
    3 => 'Objective was met but instruction exceeded the allotted time',
    2 => '',
    1 => 'No acceptable evidence was shown',
];

$kraMeta = [
    1 => ['name' => '1. Content Knowledge and Pedagogy', 'weight' => '36%'],
    2 => ['name' => '2. Learning Environment &amp; Diversity of Learners', 'weight' => '29%'],
    3 => ['name' => '3. Curriculum and Planning &amp; Assessment and Reporting', 'weight' => '7%'],
    4 => ['name' => '4. Community Linkages and Professional Engagement', 'weight' => '21%'],
    5 => ['name' => '5. Personal Growth and Professional Development', 'weight' => '7%'],
];

$objectives = [
    1 => [
        'ppst' => '1.1.2', 'kra' => 1, 'weight' => '7.14%',
        'title' => 'Applied knowledge of content within and across curriculum teaching areas',
        'quality' => cotQuality('Objective 1'), 'efficiency' => $cotEfficiency,
    ],
    2 => [
        'ppst' => '1.2.2', 'kra' => 1, 'weight' => '7.14%',
        'title' => 'Used research-based knowledge and principles of teaching and learning to enhance professional practice',
        'quality' => [
            5 => 'Integrated at a strategic level relevant and innovative research-based knowledge and principles, with clear rationale and reflection of its significant value to the teaching and learning process',
            4 => 'Applied at a contextual level research-based knowledge and principles, with appropriate and clear rationale of their use',
            3 => 'Applied at a procedural level research-based knowledge and principles, with limited but sufficient explanation of their use',
            2 => 'Adopted at a surface level basic research-based knowledge and principles, with minimal or no explanation of rationale for its use',
            1 => 'No acceptable evidence was shown',
        ],
        'efficiency' => null,
    ],
    3 => [
        'ppst' => '1.3.2', 'kra' => 1, 'weight' => '7.14%',
        'title' => 'Ensured the positive use of ICT to facilitate the teaching and learning process',
        'quality' => cotQuality(), 'efficiency' => $cotEfficiency,
    ],
    4 => [
        'ppst' => '1.4.2', 'kra' => 1, 'weight' => '7.14%',
        'title' => 'Used a range of teaching strategies that enhance learner achievement in literacy and numeracy skills',
        'quality' => cotQuality(), 'efficiency' => $cotEfficiency,
    ],
    5 => [
        'ppst' => '1.7.2', 'kra' => 1, 'weight' => '7.14%',
        'title' => 'Used effective verbal and non-verbal classroom communication strategies to support learner understanding, participation, engagement and achievement',
        'quality' => cotQuality(), 'efficiency' => $cotEfficiency,
    ],
    6 => [
        'ppst' => '2.4.2', 'kra' => 2, 'weight' => '7.14%',
        'title' => 'Maintained learning environments that nurture and inspire learners to participate, cooperate and collaborate in continued learning',
        'quality' => cotQuality(), 'efficiency' => $cotEfficiency,
    ],
    7 => [
        'ppst' => '2.5.2', 'kra' => 2, 'weight' => '7.14%',
        'title' => 'Applied a range of successful strategies that maintain learning environments that motivate learners to work productively by assuming responsibility for their own learning',
        'quality' => cotQuality(), 'efficiency' => $cotEfficiency,
    ],
    8 => [
        'ppst' => '3.3.2', 'kra' => 2, 'weight' => '7.14%',
        'title' => 'Designed, adapted and implemented teaching strategies that are responsive to learners with disabilities, giftedness and talents',
        'quality' => cotQuality(), 'efficiency' => $cotEfficiency,
    ],
    9 => [
        'ppst' => '3.4.2', 'kra' => 2, 'weight' => '7.14%',
        'title' => 'Planned and delivered teaching strategies that are responsive to the special educational needs of learners in difficult circumstances (geographic isolation, chronic illness, displacement due to armed conflict, urban resettlement or disasters, child abuse and child labor practices)',
        'quality' => cotQuality(), 'efficiency' => $cotEfficiency,
    ],
    10 => [
        'ppst' => '4.3.2', 'kra' => 3, 'weight' => '7.14%',
        'title' => 'Adapted and implemented learning programs that ensure relevance and responsiveness to the needs of all learners',
        'quality' => [
            5 => 'Implemented contextualized, localized and indigenized learning programs to ensure relevance and responsiveness to the needs of all learners, as evidenced by MOV No. 1',
            4 => 'Contextualized, localized, indigenized, adapted learning programs to ensure relevance and responsiveness to the needs of all learners, as evidenced by MOV No. 2',
            3 => 'Adapted learning programs that ensure relevance and responsiveness to the needs of all learners, as evidenced by MOV No. 3',
            2 => 'Planned for the adaptation and implementation of existing learning programs, as evidenced by MOV No. 4',
            1 => 'No acceptable evidence was shown',
        ],
        'efficiency' => null,
    ],
    11 => [
        'ppst' => '6.1.2', 'kra' => 4, 'weight' => '7.14%',
        'title' => 'Maintained learning environments that are responsive to community contexts',
        'quality' => [
            5 => 'Collaborated with the community stakeholder in the implementation / completion of a program, project, and/or activity that maintains a learning environment responsive to community contexts, as evidenced by MOV No. 4',
            4 => 'Planned with the community stakeholders a program, project, and/or activity that maintains a learning environment responsive to community contexts, as evidenced by MOV No. 3',
            3 => 'Conducted a consultative meeting with the community stakeholders on a program, project, and/or activity that maintains a learning environment responsive to community contexts, as evidenced by MOV No. 2',
            2 => 'Communicated with the community stakeholders about a program, project, and/or activity that maintains the learning environment responsive to community contexts, as evidenced by MOV No. 1',
            1 => 'No acceptable evidence was shown',
        ],
        'efficiency' => null,
    ],
    12 => [
        'ppst' => '6.3.2', 'kra' => 4, 'weight' => '7.14%',
        'title' => 'Reviewed regularly personal teaching practice using existing laws and regulations that apply to the teaching profession and the responsibilities specified in the Code of Ethics for Professional Teachers',
        'quality' => [
            5 => 'Consistently conducted review of personal teaching practice using laws and regulations that apply to the profession and the responsibilities in the Code of Ethics for Professional Teachers, as shown in the MOV submitted',
            4 => 'Frequently conducted review of personal teaching practice using laws and regulations that apply to the profession and the responsibilities in the Code of Ethics for Professional Teachers, as shown in the MOV submitted',
            3 => 'Occasionally conducted review of personal teaching practice using laws and regulations that apply to the profession and the responsibilities in the Code of Ethics for Professional Teachers, as shown in the MOV submitted',
            2 => 'Rarely conducted review of personal teaching practice using laws and regulations that apply to the profession and the responsibilities in the Code of Ethics for Professional Teachers, as shown in the MOV submitted',
            1 => 'No acceptable evidence was shown',
        ],
        'efficiency' => null,
    ],
    13 => [
        'ppst' => '6.4.2', 'kra' => 4, 'weight' => '7.14%',
        'title' => 'Complied with and implemented school policies and procedures consistently to foster harmonious relationships with learners, parents, and other stakeholders',
        'quality' => [
            5 => 'Sustained engagement with the learners, parents / guardians, and other stakeholders regarding school policies and procedures through school-community partnership/s, as evidenced by MOV No. 3 or 4',
            4 => 'Discussed consistently with learners, parents / guardians, and other stakeholders the implemented school policies and procedures, as evidenced by MOV No. 2',
            3 => 'Communicated consistently with learners, parents / guardians, and other stakeholders the implemented school policies and procedures, as evidenced by MOV No. 1',
            2 => 'Implemented school policies and procedures without involving the learners, parents / guardians, and other stakeholders',
            1 => 'No acceptable evidence was shown',
        ],
        'efficiency' => null,
    ],
    14 => [
        'ppst' => '7.2.2', 'kra' => 5, 'weight' => '7.14%',
        'title' => 'Adopted practices that uphold the dignity of teaching as a profession by exhibiting qualities such as caring attitude, respect and integrity',
        'quality' => [
            5 => 'Exhibited practices that uphold the dignity of teaching as a profession by exhibiting qualities such as caring attitude, respect, and integrity with affirmation from different school stakeholders, as evidenced by MOV No. 2',
            4 => 'Exhibited practices that uphold the dignity of teaching as a profession by exhibiting qualities such as caring attitude, respect, and integrity with affirmation from any school stakeholder, as evidenced by MOV No. 2',
            3 => 'Adopted practices that uphold the dignity of teaching as a profession by exhibiting qualities such as caring attitude, respect, and integrity, as evidenced by MOV No. 1',
            2 => 'Adopted a practice that upholds the dignity of teaching as a profession by exhibiting qualities such as caring attitude, respect, and integrity, as evidenced by MOV No. 1',
            1 => 'No acceptable evidence was shown',
        ],
        'efficiency' => null,
    ],
];

$competencies = [
    'self_management' => ['label' => 'Self-Management', 'items' => [
        'Sets personal goals and directions, needs and development.',
        'Undertakes personal actions and behavior that are clear and purposive and takes into account personal goals and values congruent to that of the organization.',
        'Displays emotional maturity and enthusiasm for and is challenged by higher goals.',
        'Prioritizes work tasks and schedules (through Gantt charts, checklists, etc.) to achieve goals.',
        'Sets high quality, challenging, realistic goals for self and others.',
    ]],
    'teamwork' => ['label' => 'Teamwork', 'items' => [
        'Willingly does his/her share of responsibility.',
        'Promotes collaboration and removes barriers to teamwork and goal accomplishment across the organization.',
        'Applies negotiation principles in arriving at win-win agreements.',
        'Drives consensus and team ownership of decisions.',
        'Works constructively and collaboratively with others and across organizations to accomplish organization goals and objectives.',
    ]],
    'prof_ethics' => ['label' => 'Professionalism and Ethics', 'items' => [
        'Demonstrates the values and behavior enshrined in the Norms and Conduct and Ethical Standards for Public Officials and Employees (RA 6713).',
        'Practices ethical and professional behavior and conduct, taking into account the impact of his/her actions and decisions.',
        'Maintains a professional image: being trustworthy, regularity of attendance and punctuality, good grooming and communication.',
        "Makes personal sacrifices to meet the organization's needs.",
        "Acts with a sense of urgency and responsibility to meet the organization's needs, improve systems, and help others improve their effectiveness.",
    ]],
    'service_orientation' => ['label' => 'Service Orientation', 'items' => [
        'Can explain and articulate organizational directions, issues and problems.',
        'Takes personal responsibility for dealing with and/or correcting customer service issues and concerns.',
        'Initiates activities that promote advocacy for men and women empowerment.',
        'Participates in updating office vision, mission, mandates and strategies based on DepEd strategies and directions.',
        'Develops and adopts service improvement programs through simplified procedures that will further enhance service delivery.',
    ]],
    'results_focus' => ['label' => 'Results Focus', 'items' => [
        'Achieves results with optimal use of time and resources most of the time.',
        'Avoids rework, mistakes and wastage through effective work methods by placing organizational needs before personal needs.',
        'Delivers error-free outputs most of the time by conforming to standard operating procedures correctly and consistently.',
        'Expresses a desire to do better and may express frustration at waste or inefficiency.',
        'Makes specific changes in the system or in own work methods to improve performance.',
    ]],
    'innovation' => ['label' => 'Innovation', 'items' => [
        'Examines the root cause of problems, suggests effective solutions, and fosters new ideas, processes and better ways of doing things.',
        'Demonstrates an ability to think "beyond the box" and continuously focuses on improving personal productivity.',
        'Promotes a creative climate and inspires co-workers to develop original ideas or solutions.',
        'Translates creative thinking into tangible changes and solutions that improve the work unit and organization.',
        'Uses ingenious methods to accomplish responsibilities, demonstrating resourcefulness with minimal resources.',
    ]],
];

function ratingSelect($name, $extraClass = '', $dataObj = '', $dataQe = '') {
    $attrs = '';
    if ($dataObj !== '') {
        $attrs .= " data-obj=\"$dataObj\" data-qe=\"$dataQe\"";
    }
    $html = "<select name=\"$name\" class=\"ipcrf-rating-select $extraClass\"$attrs>";
    $html .= '<option value="">--</option>';
    $labels = [5 => '5 - Outstanding', 4 => '4 - Very Satisfactory', 3 => '3 - Satisfactory', 2 => '2 - Unsatisfactory', 1 => '1 - Poor'];
    foreach ($labels as $val => $lab) {
        $html .= "<option value=\"$val\">$lab</option>";
    }
    $html .= '</select>';
    return $html;
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
        <button type="button" class="wizard-tab" data-step="5">Part I</button>
        <button type="button" class="wizard-tab" data-step="6">Part II</button>
        <button type="button" class="wizard-tab" data-step="7">Part III</button>
        <button type="button" class="wizard-tab" data-step="8">Part IV</button>
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
                <h2>Step 2: Quick Entry (saved to your record)</h2>

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

                <p class="step-hint">Continue to <strong>Part I</strong> to fill out the complete IPCRF Rating Sheet (all 14 objectives), then Parts II-IV.</p>

                <div class="wizard-actions split">
                    <button type="button" class="btn-back" data-back="3">Back</button>
                    <button type="button" class="btn-next" data-next="5">Next: Part I</button>
                </div>

                <?php echo $message; ?>
            </div>
        </section>

        <section class="wizard-step" data-step="5">
            <div class="step5-wrap">
                <h2>PART I: Official IPCRF Rating Sheet</h2>
                <p class="step-hint step-hint--dark">Reference rubric text is fixed per DepEd's official form. Fill in <strong>Actual Results</strong> and select a <strong>Rating</strong> for every Quality/Efficiency criterion — Ave and Score are computed automatically.</p>

                <table class="step5-table ipcrf-header-table">
                    <tr>
                        <th colspan="3">Name of Employee:</th>
                        <td colspan="4"><input type="text" name="s5_employee_name"></td>
                        <th>RATER Last Name:</th>
                        <td><input type="text" name="s5_rater_last"></td>
                        <th>First:</th>
                        <td><input type="text" name="s5_rater_first"></td>
                        <th>Middle:</th>
                        <td><input type="text" name="s5_rater_middle"></td>
                    </tr>
                    <tr>
                        <th colspan="3">Position:</th>
                        <td colspan="4"><input type="text" name="s5_employee_position"></td>
                        <th>Position:</th>
                        <td><input type="text" name="s5_rater_position"></td>
                        <th>Email:</th>
                        <td colspan="3"><input type="email" name="s5_rater_email"></td>
                    </tr>
                    <tr>
                        <th colspan="3">Bureau/Center/Service/Division:</th>
                        <td colspan="4"><input type="text" name="s5_bureau"></td>
                        <th>Date of Review:</th>
                        <td><input type="text" name="s5_date_review" placeholder="YYYY-MM-DD"></td>
                        <td colspan="4"></td>
                    </tr>
                    <tr>
                        <th colspan="3">Rating Period:</th>
                        <td colspan="11"><input type="text" name="s5_rating_period" placeholder="SY 2025-2026"></td>
                    </tr>
                </table>

                <div class="step5-table-scroll">
                    <table class="step5-table ipcrf-rating-table">
                        <tr>
                            <th>QET</th>
                            <th>Outstanding (5)</th>
                            <th>Very Satisfactory (4)</th>
                            <th>Satisfactory (3)</th>
                            <th>Unsatisfactory (2)</th>
                            <th>Poor (1)</th>
                            <th>Actual Results</th>
                            <th>Rating</th>
                            <th>Ave</th>
                            <th>Score</th>
                        </tr>
                        <?php
                        $prevKra = null;
                        foreach ($objectives as $num => $o):
                            if ($o['kra'] !== $prevKra):
                                $prevKra = $o['kra'];
                                $meta = $kraMeta[$o['kra']];
                        ?>
                        <tr class="ipcrf-kra-banner">
                            <td colspan="10">KRA <?php echo $o['kra']; ?> &mdash; <?php echo $meta['name']; ?> (Weight per KRA: <?php echo $meta['weight']; ?>)</td>
                        </tr>
                        <?php endif; ?>
                        <tr class="ipcrf-obj-banner">
                            <td colspan="10">Objective <?php echo $num; ?> (PPST <?php echo htmlspecialchars($o['ppst']); ?>): <?php echo htmlspecialchars($o['title']); ?> &mdash; Weight per Objective: <?php echo $o['weight']; ?></td>
                        </tr>
                        <?php $hasEff = $o['efficiency'] !== null; $rowspan = $hasEff ? 2 : 1; ?>
                        <tr>
                            <td>Quality</td>
                            <?php foreach ([5,4,3,2,1] as $lvl): ?>
                                <td class="rubric-cell"><?php echo htmlspecialchars($o['quality'][$lvl]); ?></td>
                            <?php endforeach; ?>
                            <td><textarea name="s5_actual_<?php echo $num; ?>_q" rows="2" placeholder="Describe actual results for Quality"></textarea></td>
                            <td><?php echo ratingSelect("s5_rating_{$num}_q", '', $num, 'q'); ?></td>
                            <td rowspan="<?php echo $rowspan; ?>"><input type="text" id="s5_ave_<?php echo $num; ?>" name="s5_ave_<?php echo $num; ?>" readonly></td>
                            <td rowspan="<?php echo $rowspan; ?>"><input type="text" id="s5_score_<?php echo $num; ?>" name="s5_score_<?php echo $num; ?>" readonly></td>
                        </tr>
                        <?php if ($hasEff): ?>
                        <tr>
                            <td>Efficiency</td>
                            <?php foreach ([5,4,3,2,1] as $lvl): ?>
                                <td class="rubric-cell"><?php echo htmlspecialchars($o['efficiency'][$lvl]); ?></td>
                            <?php endforeach; ?>
                            <td><textarea name="s5_actual_<?php echo $num; ?>_e" rows="2" placeholder="Describe actual results for Efficiency"></textarea></td>
                            <td><?php echo ratingSelect("s5_rating_{$num}_e", '', $num, 'e'); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                        <tr class="ipcrf-final-row">
                            <th colspan="8" class="text-right">Final Rating (Part I)</th>
                            <td colspan="2">
                                <span id="ipcrf_final_rating">&mdash;</span> &nbsp; <span id="ipcrf_final_adjectival">&mdash;</span>
                                <input type="hidden" id="s5_final_numeric_hidden" name="s5_final_numeric">
                                <input type="hidden" id="s5_final_adjectival_hidden" name="s5_final_adjectival">
                            </td>
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
                    <button type="button" class="btn-next" data-next="6">Next: Part II</button>
                </div>
            </div>
        </section>

        <section class="wizard-step" data-step="6">
            <div class="step5-wrap">
                <h2>PART II: Core Behavioral Competencies</h2>
                <p class="step-hint step-hint--dark">Check every indicator you demonstrated during the performance cycle. The count per competency is totaled automatically (this does not affect the numerical rating in Part I).</p>

                <div class="comp-grid">
                    <?php foreach ($competencies as $key => $c): ?>
                    <div class="comp-panel">
                        <div class="comp-panel__head">
                            <h3><?php echo htmlspecialchars($c['label']); ?></h3>
                            <label class="comp-total-label">Total demonstrated:
                                <input type="text" id="comp_total_<?php echo $key; ?>" name="comp_total_<?php echo $key; ?>" readonly>
                            </label>
                        </div>
                        <?php foreach ($c['items'] as $i => $item): ?>
                            <label class="comp-item">
                                <input type="checkbox" class="comp-checkbox" data-comp="<?php echo $key; ?>" name="comp_<?php echo $key; ?>[]" value="<?php echo $i + 1; ?>">
                                <?php echo ($i + 1) . '. ' . htmlspecialchars($item); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="wizard-actions split">
                    <button type="button" class="btn-back" data-back="5">Back</button>
                    <button type="button" class="btn-next" data-next="7">Next: Part III</button>
                </div>
            </div>
        </section>

        <section class="wizard-step" data-step="7">
            <div class="step5-wrap">
                <h2>PART III: Summary of Ratings for Discussion</h2>
                <p class="step-hint step-hint--dark">Auto-generated from Part I. Go back and complete every objective's rating in Part I to fill this in.</p>

                <div class="step5-table-scroll">
                    <table class="step5-table ipcrf-summary-table">
                        <tr>
                            <th>KRA</th>
                            <th>Weight per KRA</th>
                            <th>Objective</th>
                            <th>PPST</th>
                            <th>Weight</th>
                            <th>Q</th>
                            <th>E</th>
                            <th>Ave</th>
                            <th>Score</th>
                            <th>Adjectival Rating</th>
                        </tr>
                        <?php foreach ($objectives as $num => $o): ?>
                        <tr>
                            <td>KRA <?php echo $o['kra']; ?></td>
                            <td><?php echo $kraMeta[$o['kra']]['weight']; ?></td>
                            <td class="text-left">Obj. <?php echo $num; ?> &mdash; <?php echo htmlspecialchars($o['title']); ?></td>
                            <td><?php echo htmlspecialchars($o['ppst']); ?></td>
                            <td><?php echo $o['weight']; ?></td>
                            <td id="step7_q_<?php echo $num; ?>">&mdash;</td>
                            <td id="step7_e_<?php echo $num; ?>">&mdash;</td>
                            <td id="step7_ave_<?php echo $num; ?>">&mdash;</td>
                            <td id="step7_score_<?php echo $num; ?>">&mdash;</td>
                            <td id="step7_adj_<?php echo $num; ?>">&mdash;</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="ipcrf-final-row">
                            <th colspan="8" class="text-right">Final Rating</th>
                            <td id="step7_final_rating">&mdash;</td>
                            <td id="step7_final_adjectival">&mdash;</td>
                        </tr>
                    </table>
                </div>

                <div class="step5-signatory-grid">
                    <div class="sign-card">
                        <label>Ratee:</label>
                        <input type="text" name="s7_sign_ratee_name">
                    </div>
                    <div class="sign-card">
                        <label>Rater:</label>
                        <input type="text" name="s7_sign_rater_name">
                    </div>
                    <div class="sign-card">
                        <label>Approving Authority:</label>
                        <input type="text" name="s7_sign_approver_name">
                    </div>
                </div>

                <div class="wizard-actions split">
                    <button type="button" class="btn-back" data-back="6">Back</button>
                    <button type="button" class="btn-next" data-next="8">Next: Part IV</button>
                </div>
            </div>
        </section>

        <section class="wizard-step" data-step="8">
            <div class="step5-wrap">
                <h2>PART IV: Development Plans</h2>
                <p class="step-hint step-hint--dark">A. Functional Competencies &mdash; list your strengths and development needs per objective, with the action plan, timeline, and resources needed. Use "Add Row" for more entries.</p>

                <div class="step5-table-scroll">
                    <table class="step5-table devplan-table">
                        <tr>
                            <th>Strengths</th>
                            <th>Development Needs</th>
                            <th>Learning Objectives</th>
                            <th>Intervention</th>
                            <th>Timeline</th>
                            <th>Resources Needed</th>
                        </tr>
                        <tbody id="devplan_functional_body">
                        <?php for ($r = 1; $r <= 6; $r++): ?>
                        <tr>
                            <td><input type="text" name="s8_func_strength_<?php echo $r; ?>"></td>
                            <td><input type="text" name="s8_func_devneed_<?php echo $r; ?>"></td>
                            <td><input type="text" name="s8_func_learnobj_<?php echo $r; ?>"></td>
                            <td><input type="text" name="s8_func_intervention_<?php echo $r; ?>"></td>
                            <td><input type="text" name="s8_func_timeline_<?php echo $r; ?>" placeholder="Year-round"></td>
                            <td><input type="text" name="s8_func_resources_<?php echo $r; ?>"></td>
                        </tr>
                        <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
                <div class="wizard-actions">
                    <button type="button" class="btn-addrow" onclick="addDevPlanRow('devplan_functional_body','s8_func')">+ Add Row</button>
                </div>

                <p class="step-hint step-hint--dark">B. Core Behavioral Competencies &mdash; matched to the six Part II competency categories.</p>
                <div class="step5-table-scroll">
                    <table class="step5-table devplan-table">
                        <tr>
                            <th>Strengths</th>
                            <th>Development Needs</th>
                            <th>Learning Objectives</th>
                            <th>Intervention</th>
                            <th>Timeline</th>
                            <th>Resources Needed</th>
                        </tr>
                        <?php $b = 1; foreach ($competencies as $key => $c): ?>
                        <tr>
                            <td><input type="text" name="s8_core_strength_<?php echo $b; ?>" value="<?php echo htmlspecialchars($c['label']); ?>"></td>
                            <td><input type="text" name="s8_core_devneed_<?php echo $b; ?>"></td>
                            <td><input type="text" name="s8_core_learnobj_<?php echo $b; ?>"></td>
                            <td><input type="text" name="s8_core_intervention_<?php echo $b; ?>"></td>
                            <td><input type="text" name="s8_core_timeline_<?php echo $b; ?>" placeholder="Year-round"></td>
                            <td><input type="text" name="s8_core_resources_<?php echo $b; ?>"></td>
                        </tr>
                        <?php $b++; endforeach; ?>
                    </table>
                </div>

                <div class="step5-signatory-grid">
                    <div class="sign-card">
                        <label>Ratee:</label>
                        <input type="text" name="s8_sign_ratee_name">
                    </div>
                    <div class="sign-card">
                        <label>Rater:</label>
                        <input type="text" name="s8_sign_rater_name">
                    </div>
                    <div class="sign-card">
                        <label>Approving Authority:</label>
                        <input type="text" name="s8_sign_approver_name">
                    </div>
                </div>

                <div class="wizard-actions split">
                    <button type="button" class="btn-back" data-back="7">Back</button>
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

        if (String(stepNumber) === '7') {
            updateStep7Summary();
        }
    }

    <?php if ($jumpToLastStep): ?>
    showStep('8');
    <?php endif; ?>

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

    // ---- Part I: auto-compute Ave / Score / Final Rating ----
    const OBJECTIVE_COUNT = 14;
    const OBJECTIVE_WEIGHT = 1 / OBJECTIVE_COUNT;

    window.recalcObjective = function (obj) {
        const qSel = document.querySelector(`select[name="s5_rating_${obj}_q"]`);
        const eSel = document.querySelector(`select[name="s5_rating_${obj}_e"]`);
        const q = qSel && qSel.value ? parseFloat(qSel.value) : null;
        const e = eSel && eSel.value ? parseFloat(eSel.value) : null;
        const vals = [q, e].filter(v => v !== null && !isNaN(v));

        const aveField = document.getElementById(`s5_ave_${obj}`);
        const scoreField = document.getElementById(`s5_score_${obj}`);
        if (!aveField || !scoreField) return;

        if (vals.length === 0) {
            aveField.value = '';
            scoreField.value = '';
        } else {
            const ave = vals.reduce((a, b) => a + b, 0) / vals.length;
            const score = ave * OBJECTIVE_WEIGHT;
            aveField.value = ave.toFixed(3);
            scoreField.value = score.toFixed(3);
        }
        recalcFinal();
    };

    function adjectivalFor(score) {
        if (score >= 4.5) return 'Outstanding';
        if (score >= 3.5) return 'Very Satisfactory';
        if (score >= 2.5) return 'Satisfactory';
        if (score >= 1.5) return 'Unsatisfactory';
        return 'Poor';
    }

    function recalcFinal() {
        let sum = 0, filled = 0;
        for (let i = 1; i <= OBJECTIVE_COUNT; i++) {
            const sf = document.getElementById(`s5_score_${i}`);
            if (sf && sf.value !== '') {
                sum += parseFloat(sf.value);
                filled++;
            }
        }
        const finalDisplay = document.getElementById('ipcrf_final_rating');
        const adjDisplay = document.getElementById('ipcrf_final_adjectival');
        const complete = filled === OBJECTIVE_COUNT;

        if (finalDisplay) finalDisplay.textContent = filled ? sum.toFixed(3) : '—';
        if (adjDisplay) adjDisplay.textContent = complete ? adjectivalFor(sum) : '—';

        const hiddenNum = document.getElementById('s5_final_numeric_hidden');
        const hiddenAdj = document.getElementById('s5_final_adjectival_hidden');
        if (hiddenNum) hiddenNum.value = filled ? sum.toFixed(3) : '';
        if (hiddenAdj) hiddenAdj.value = complete ? adjectivalFor(sum) : '';
    }

    window.updateStep7Summary = function () {
        let sum = 0, filled = 0;
        for (let i = 1; i <= OBJECTIVE_COUNT; i++) {
            const qSel = document.querySelector(`select[name="s5_rating_${i}_q"]`);
            const eSel = document.querySelector(`select[name="s5_rating_${i}_e"]`);
            const aveField = document.getElementById(`s5_ave_${i}`);
            const scoreField = document.getElementById(`s5_score_${i}`);

            const qText = document.getElementById(`step7_q_${i}`);
            const eText = document.getElementById(`step7_e_${i}`);
            const aveText = document.getElementById(`step7_ave_${i}`);
            const scoreText = document.getElementById(`step7_score_${i}`);
            const adjText = document.getElementById(`step7_adj_${i}`);
            if (!qText) continue;

            qText.textContent = (qSel && qSel.value) ? qSel.value : '—';
            eText.textContent = (eSel && eSel.value) ? eSel.value : (eSel ? '—' : 'N/A');
            aveText.textContent = (aveField && aveField.value) ? aveField.value : '—';
            scoreText.textContent = (scoreField && scoreField.value) ? scoreField.value : '—';
            adjText.textContent = (aveField && aveField.value) ? adjectivalFor(parseFloat(aveField.value)) : '—';

            if (scoreField && scoreField.value !== '') {
                sum += parseFloat(scoreField.value);
                filled++;
            }
        }
        const complete = filled === OBJECTIVE_COUNT;
        document.getElementById('step7_final_rating').textContent = filled ? sum.toFixed(3) : '—';
        document.getElementById('step7_final_adjectival').textContent = complete ? adjectivalFor(sum) : '—';
    };

    document.querySelectorAll('.ipcrf-rating-select').forEach(sel => {
        sel.addEventListener('change', function () {
            recalcObjective(this.dataset.obj);
        });
    });

    // ---- Part II: auto-count checked competency indicators ----
    document.querySelectorAll('.comp-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const key = this.dataset.comp;
            const total = document.querySelectorAll(`.comp-checkbox[data-comp="${key}"]:checked`).length;
            const totalField = document.getElementById(`comp_total_${key}`);
            if (totalField) totalField.value = total;
        });
    });

    // ---- Part IV: add a blank row to the Functional Competencies table ----
    window.addDevPlanRow = function (tbodyId, prefix) {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        const rows = tbody.querySelectorAll('tr');
        const newIndex = rows.length + 1;
        const clone = rows[rows.length - 1].cloneNode(true);
        clone.querySelectorAll('input, textarea').forEach(el => {
            el.value = '';
            if (el.name) el.name = el.name.replace(/_\d+$/, '_' + newIndex);
        });
        tbody.appendChild(clone);
    };

    // ---- Guard against duplicate entries from a double click / double Enter ----
    const wizardForm = document.getElementById('ipcrfWizardForm');
    const submitBtn = wizardForm ? wizardForm.querySelector('button[name="submit_ipcrf"]') : null;
    if (wizardForm && submitBtn) {
        wizardForm.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting…';
        });
    }
})();
</script>
</body>
</html>
