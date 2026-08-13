<?php

/**
 * Reference data for the Official IPCRF Rating Sheet (Part I). Text mirrors
 * the official DepEd IPCRF rubric wording. Shared between ipcrf_form.php
 * (renders it as a fillable rating sheet) and export_entry_csv.php (labels
 * each exported row with the same PPST/KRA/title/weight), so both stay in
 * sync from one source instead of two copies drifting apart.
 */

function ipcrf_kra_meta() {
    return [
        1 => ['name' => '1. Content Knowledge and Pedagogy', 'weight' => '36%'],
        2 => ['name' => '2. Learning Environment &amp; Diversity of Learners', 'weight' => '29%'],
        3 => ['name' => '3. Curriculum and Planning &amp; Assessment and Reporting', 'weight' => '7%'],
        4 => ['name' => '4. Community Linkages and Professional Engagement', 'weight' => '21%'],
        5 => ['name' => '5. Personal Growth and Professional Development', 'weight' => '7%'],
    ];
}

function ipcrf_cot_quality($label = 'the objective') {
    return [
        5 => "Demonstrated Level 7 in $label as shown in COT rating sheets / inter-observer agreement forms",
        4 => "Demonstrated Level 6 in $label as shown in COT rating sheets / inter-observer agreement forms",
        3 => "Demonstrated Level 5 in $label as shown in COT rating sheets / inter-observer agreement forms",
        2 => "Demonstrated Level 4 in $label as shown in COT rating sheets / inter-observer agreement forms",
        1 => "Demonstrated Level 3 in $label as shown in COT rating sheets / inter-observer agreement forms, or No acceptable evidence was shown",
    ];
}

function ipcrf_part1_objectives() {
    $cotEfficiency = [
        5 => 'Objective was met within the allotted time',
        4 => '',
        3 => 'Objective was met but instruction exceeded the allotted time',
        2 => '',
        1 => 'No acceptable evidence was shown',
    ];

    return [
        1 => [
            'ppst' => '1.1.2', 'kra' => 1, 'weight' => '7.14%',
            'title' => 'Applied knowledge of content within and across curriculum teaching areas',
            'quality' => ipcrf_cot_quality('Objective 1'), 'efficiency' => $cotEfficiency,
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
            'quality' => ipcrf_cot_quality(), 'efficiency' => $cotEfficiency,
        ],
        4 => [
            'ppst' => '1.4.2', 'kra' => 1, 'weight' => '7.14%',
            'title' => 'Used a range of teaching strategies that enhance learner achievement in literacy and numeracy skills',
            'quality' => ipcrf_cot_quality(), 'efficiency' => $cotEfficiency,
        ],
        5 => [
            'ppst' => '1.7.2', 'kra' => 1, 'weight' => '7.14%',
            'title' => 'Used effective verbal and non-verbal classroom communication strategies to support learner understanding, participation, engagement and achievement',
            'quality' => ipcrf_cot_quality(), 'efficiency' => $cotEfficiency,
        ],
        6 => [
            'ppst' => '2.4.2', 'kra' => 2, 'weight' => '7.14%',
            'title' => 'Maintained learning environments that nurture and inspire learners to participate, cooperate and collaborate in continued learning',
            'quality' => ipcrf_cot_quality(), 'efficiency' => $cotEfficiency,
        ],
        7 => [
            'ppst' => '2.5.2', 'kra' => 2, 'weight' => '7.14%',
            'title' => 'Applied a range of successful strategies that maintain learning environments that motivate learners to work productively by assuming responsibility for their own learning',
            'quality' => ipcrf_cot_quality(), 'efficiency' => $cotEfficiency,
        ],
        8 => [
            'ppst' => '3.3.2', 'kra' => 2, 'weight' => '7.14%',
            'title' => 'Designed, adapted and implemented teaching strategies that are responsive to learners with disabilities, giftedness and talents',
            'quality' => ipcrf_cot_quality(), 'efficiency' => $cotEfficiency,
        ],
        9 => [
            'ppst' => '3.4.2', 'kra' => 2, 'weight' => '7.14%',
            'title' => 'Planned and delivered teaching strategies that are responsive to the special educational needs of learners in difficult circumstances (geographic isolation, chronic illness, displacement due to armed conflict, urban resettlement or disasters, child abuse and child labor practices)',
            'quality' => ipcrf_cot_quality(), 'efficiency' => $cotEfficiency,
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
}
