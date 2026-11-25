<?php

namespace App\Services\Students;

use App\Services\Terms\TermRegistrationService;

/**
 * The student-portal quiz: how to pay fees, evaluate trainers and read the dashboard.
 * Numbers come from config and the term rules, so the answers stay true if they change.
 * Each question: text, options, index of the correct option, and the explanation shown after marking.
 */
class PortalQuiz
{
    public const KEY = 'portal-basics';

    public function questions(?string $studentNumber = null): array
    {
        $paybill = config('kcb.paybill');
        $till = config('kcb.tills.school_fees');
        $number = $studentNumber ?: 'your student number';
        $half = TermRegistrationService::THRESHOLD_PERCENT;
        $days = TermRegistrationService::OPENS_DAYS_BEFORE_END;

        return [
            [
                'q' => 'Which M-Pesa Paybill number do you use to pay school fees?',
                'options' => [$till, $paybill, '8118445', '247247'],
                'answer' => 1,
                'why' => "School fees go to Paybill {$paybill}.",
            ],
            [
                'q' => 'What do you type as the Account number when paying by Paybill?',
                'options' => ['Your phone number', 'Your full name', "{$till}#{$number}", 'Only the word FEES'],
                'answer' => 2,
                'why' => "The account is {$till}# followed by your student number, for example {$till}#{$number}. That is how the money reaches your account automatically.",
            ],
            [
                'q' => 'What is the quickest way to pay from the student portal?',
                'options' => [
                    'Send the money to a staff member\'s phone',
                    'Press "Pay fees", enter the amount and your M-Pesa number, then enter your PIN on the prompt that comes to your phone',
                    'Email the finance office',
                    'Upload a photo of the M-Pesa message',
                ],
                'answer' => 1,
                'why' => 'Use "Pay fees" on your dashboard or the Fee Statement page. A PIN prompt comes to your phone; after you enter the PIN the payment is recorded by itself.',
            ],
            [
                'q' => 'You paid by Paybill but typed the wrong account number. What should you do?',
                'options' => [
                    'Nothing: the money is lost',
                    'Pay again with the right account and forget the first payment',
                    'Contact the finance office with your M-Pesa message so they can put the payment on your account',
                    'Ask a classmate to pay for you',
                ],
                'answer' => 2,
                'why' => 'Payments with a wrong account number wait with the finance office. Show them the M-Pesa message and they will apply it to your fees.',
            ],
            [
                'q' => 'How do you know your payment has been received?',
                'options' => [
                    'You get an SMS, and it shows under Recent payments and on your Fee Statement',
                    'The trainer tells you in class',
                    'You have to wait until the end of the term',
                    'You cannot know',
                ],
                'answer' => 0,
                'why' => 'Every payment is confirmed by SMS and appears at once under Recent payments on your dashboard and on your Fee Statement.',
            ],
            [
                'q' => 'When can you register for your next term?',
                'options' => [
                    'Any time, even without paying',
                    'Only after the current term is fully paid',
                    "When at least {$half}% of the current term's fee is paid; registration opens {$days} days before the current term ends",
                    'Only on the first day of the new term',
                ],
                'answer' => 2,
                'why' => "You need {$half}% of the current term paid. The \"Next term\" box on your dashboard shows when registration opens and what is still needed.",
            ],
            [
                'q' => 'What do you need before you can download your exam card for a term?',
                'options' => [
                    'Only 50% of the term paid',
                    "The term's fee 100% paid AND all your trainers for that term evaluated",
                    'Permission from the class representative',
                    'Nothing: everyone gets one',
                ],
                'answer' => 1,
                'why' => 'The exam card unlocks when the term is fully paid and you have evaluated the trainer of every unit you took that term.',
            ],
            [
                'q' => 'Where do you evaluate your trainers?',
                'options' => [
                    'On your dashboard, in the Exam card box: "Evaluate your trainers"',
                    'In the Library page',
                    'By sending an SMS to the college',
                    'On paper at the reception',
                ],
                'answer' => 0,
                'why' => 'Open "Evaluate your trainers" in the Exam card box on your dashboard. You rate the trainer of each unit you took that term.',
            ],
            [
                'q' => 'Who can see which student wrote an evaluation?',
                'options' => ['The trainer', 'The college administration', 'Your classmates', 'Nobody: evaluations are anonymous'],
                'answer' => 3,
                'why' => 'Evaluations are anonymous. Trainers and the administration only see combined results, and only after at least three students have answered, so you can be honest.',
            ],
            [
                'q' => 'On your dashboard, what does the "Fee balance" box show?',
                'options' => [
                    'Your M-Pesa balance',
                    'What you still owe the college, with the Paybill and account to use',
                    'How much your library fine is',
                    'Your marks for the term',
                ],
                'answer' => 1,
                'why' => 'Fee balance is what you still owe for your terms. Under it you see the Paybill and the exact account number to pay with.',
            ],
        ];
    }

    /** @param array<int, int|string|null> $answers  question index => chosen option index */
    public function mark(array $answers, ?string $studentNumber = null): array
    {
        $rows = [];
        $score = 0;
        foreach ($this->questions($studentNumber) as $i => $q) {
            $chosen = isset($answers[$i]) && $answers[$i] !== '' ? (int) $answers[$i] : null;
            $right = $chosen === $q['answer'];
            $score += $right ? 1 : 0;
            $rows[] = ['chosen' => $chosen, 'right' => $right];
        }

        return ['score' => $score, 'total' => count($rows), 'rows' => $rows];
    }
}
