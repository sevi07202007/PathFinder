<?php
declare(strict_types=1);

/* The existing four-question frontend scoring logic, retained verbatim in PHP. */
function assessmentQuestionTexts(): array
{
    return [
        'What type of work energizes you the most?',
        'Which environment sounds most enjoyable?',
        'How do you usually approach a challenge?',
        'What kind of projects would you most likely enjoy?',
    ];
}

function careerDisplayMetadata(): array
{
    return [
        'UX Designer' => ['id'=>'ux-designer','category'=>'Design','salary'=>'₱60k – ₱120k','outlook'=>'High demand','bestFor'=>'Creative people who enjoy empathy, problem-solving, and visual storytelling.','path'=>'Multimedia Arts, Interaction Design, Psychology, Information Design','skills'=>['Figma','User research','Wireframing','Accessibility','Prototyping'],'color'=>'#8b5cf6'],
        'Software Engineer' => ['id'=>'software-engineer','category'=>'Technology','salary'=>'₱80k – ₱180k','outlook'=>'Very high demand','bestFor'=>'Logical thinkers who enjoy coding, debugging, and turning ideas into tools.','path'=>'Computer Science, Information Technology, Software Engineering','skills'=>['JavaScript','Web Development','APIs','Algorithms','Testing'],'color'=>'#3b82f6'],
        'Data Analyst' => ['id'=>'data-analyst','category'=>'Analytics','salary'=>'₱65k – ₱150k','outlook'=>'Strong growth','bestFor'=>'People who enjoy patterns, spreadsheets, reporting, and asking “why?”','path'=>'Statistics, Economics, Data Science, Business Analytics','skills'=>['Excel','SQL','Dashboarding','Statistics','Visualization'],'color'=>'#14b8a6'],
        'Product Manager' => ['id'=>'product-manager','category'=>'Business','salary'=>'₱90k – ₱200k','outlook'=>'High demand','bestFor'=>'Strategic thinkers who enjoy planning, communication, and balancing priorities.','path'=>'Business Administration, Marketing, Management, Computer Science','skills'=>['Roadmapping','Research','Stakeholder management','Analytics','Planning'],'color'=>'#f59e0b'],
        'Cybersecurity Specialist' => ['id'=>'cybersecurity-specialist','category'=>'Technology','salary'=>'₱70k – ₱170k','outlook'=>'Very high demand','bestFor'=>'Analytical minds who enjoy problem-solving, security, and protecting systems.','path'=>'Computer Science, Information Security, IT, Network Engineering','skills'=>['Threat analysis','Networking','Risk assessment','Ethical hacking','Security tools'],'color'=>'#ef4444'],
        'Content Strategist' => ['id'=>'content-strategist','category'=>'Communication','salary'=>'₱50k – ₱110k','outlook'=>'Stable growth','bestFor'=>'People who enjoy writing, storytelling, planning, and understanding audience needs.','path'=>'Communication, Marketing, Journalism, English','skills'=>['Content planning','SEO','Copywriting','Campaign planning','Brand storytelling'],'color'=>'#ec4899'],
    ];
}

function questionScores(): array
{
    return [
        ['Solving problems with logic and code' => ['tech'=>2,'analytical'=>1,'problem'=>1], 'Helping people and understanding their needs' => ['people'=>2,'creative'=>1,'leadership'=>1], 'Designing ideas and making them visually appealing' => ['creative'=>2,'people'=>1,'visual'=>1], 'Planning strategy and making decisions' => ['leadership'=>2,'strategic'=>1,'problem'=>1]],
        ['A fast-moving tech workspace with digital tools' => ['tech'=>2,'analytical'=>1,'detail'=>1], 'A collaborative team where I can communicate and inspire' => ['people'=>2,'leadership'=>1,'communication'=>1], 'A creative studio where I can experiment and design' => ['creative'=>2,'visual'=>1,'communication'=>1], 'A strategic office where I evaluate numbers and plans' => ['analytical'=>2,'strategic'=>1,'detail'=>1]],
        ['Break it apart and find the root cause' => ['problem'=>2,'analytical'=>1,'tech'=>1], 'Talk it through and get feedback from others' => ['people'=>2,'communication'=>1,'leadership'=>1], 'Imagine a better solution and make it look good' => ['creative'=>2,'visual'=>1,'problem'=>1], 'Analyze trends and choose the smartest direction' => ['strategic'=>2,'analytical'=>1,'leadership'=>1]],
        ['Building apps, platforms, or systems' => ['tech'=>2,'problem'=>1,'analytical'=>1], 'Planning events, campaigns, or community programs' => ['people'=>2,'communication'=>1,'leadership'=>1], 'Designing visuals, layouts, or brand materials' => ['creative'=>2,'visual'=>1,'people'=>1], 'Reviewing data and recommending solutions' => ['analytical'=>2,'detail'=>1,'strategic'=>1]],
    ];
}

function legacyCareerTraits(): array
{
    return [
        'UX Designer' => ['creative','people','problem-solving','tech'],
        'Software Engineer' => ['tech','analytical','problem-solving','leadership'],
        'Data Analyst' => ['analytical','problem-solving','detail','tech'],
        'Product Manager' => ['leadership','people','strategic','problem-solving'],
        'Cybersecurity Specialist' => ['tech','analytical','detail','problem-solving'],
        'Content Strategist' => ['creative','people','strategic','communication'],
    ];
}

function rankCareers(array $answerLabels, array $careers): array
{
    $traitTotals = [];
    $questions = questionScores();
    foreach ($answerLabels as $label) {
        foreach ($questions as $question) {
            if (isset($question[$label])) {
                foreach ($question[$label] as $trait => $value) {
                    $traitTotals[$trait] = ($traitTotals[$trait] ?? 0) + $value;
                }
                break;
            }
        }
    }

    $traitsByCareer = legacyCareerTraits();
    foreach ($careers as &$career) {
        $traits = $traitsByCareer[$career['Career_Title']] ?? [];
        $career['score'] = array_sum(array_map(fn($trait) => $traitTotals[$trait] ?? 0, $traits));
        $max = 0;
        foreach ($questions as $question) {
            $best = 0;
            foreach ($question as $option) {
                $best = max($best, array_sum(array_map(fn($trait) => $option[$trait] ?? 0, $traits)));
            }
            $max += $best;
        }
        $career['matchPercentage'] = $max === 0 ? 0 : (int) round(($career['score'] / $max) * 100);
    }
    unset($career);
    usort($careers, function (array $a, array $b): int {
        $scoreOrder = $b['score'] <=> $a['score'];
        return $scoreOrder !== 0 ? $scoreOrder : ($a['Career_ID'] <=> $b['Career_ID']);
    });
    return $careers;
}
