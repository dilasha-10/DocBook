<?php

// ═══════════════════════════════════════════════════════════════════

if (!function_exists('chatbot_faq_match')):

//  ChatbotController.php
// ═══════════════════════════════════════════════════════════════════


// ── FAQ knowledge base ───────────────────────────────────────────────

function chatbot_faq_match(string $input): ?string
{
    $msg = strtolower(trim($input));

    $faqs = [
        // Greetings
        [
            'patterns' => ['hello', 'hi ', '^hi$', 'hey', 'good morning', 'good afternoon', 'good evening', 'howdy'],
            'answer'   => "Hi there! I'm the DocBook assistant. I can help you with:\n• Booking, cancelling or rescheduling appointments\n• Payment and refund questions\n• Finding doctors\n• Account and profile settings\n\nWhat do you need help with?",
        ],
        // Booking
        [
            'patterns' => ['how.*book', 'book.*appointment', 'make.*appointment', 'schedule.*appointment', 'how do i book', 'want to book'],
            'answer'   => "To book an appointment:\n1. Click **Find Doctors** in the top menu.\n2. Choose a specialization.\n3. Select a doctor and pick an available slot.\n4. Confirm and complete payment via eSewa if required.\n\nYou will see the booking under **Upcoming Appointments** on your dashboard once the doctor accepts.",
        ],
        // Cancel
        [
            'patterns' => ['cancel', 'cancell', 'how.*cancel', 'delete.*appointment'],
            'answer'   => "To cancel an appointment:\n1. Go to your **Dashboard**.\n2. Find the appointment in the Upcoming list.\n3. Click **Cancel** and confirm.\n\nCancellations cannot be undone. For refund requests please contact admin support.",
        ],
        // Reschedule
        [
            'patterns' => ['reschedul', 'change.*date', 'change.*time', 'move.*appointment', 'postpone'],
            'answer'   => "To reschedule an appointment:\n1. Go to your **Dashboard**.\n2. Find the upcoming appointment.\n3. Click **Reschedule** and choose a new date/time.\n\nThe doctor will be notified automatically.",
        ],
        // Payment / eSewa
        [
            'patterns' => ['pay', 'payment', 'esewa', 'refund', 'fee', 'cost', 'price', 'charge', 'how much'],
            'answer'   => "Payments are processed securely through **eSewa**. You will be redirected to eSewa at checkout.\n\nFor refund requests, contact admin — refunds are reviewed within 2 business days.",
        ],
        // Appointment status
        [
            'patterns' => ['status', 'pending', 'confirm', 'approved', 'what.*status', 'check.*appointment'],
            'answer'   => "Appointment statuses:\n• **Pending** — waiting for doctor to confirm\n• **Confirmed** — doctor has accepted\n• **Completed** — visit is done\n• **Cancelled** — appointment was cancelled\n\nCheck your status anytime on the **Dashboard**.",
        ],
        // Find a doctor
        [
            'patterns' => ['find.*doctor', 'which doctor', 'specialist', 'search.*doctor', 'doctor.*available', 'available doctor'],
            'answer'   => "Browse doctors by specialization on the **Find Doctors** page. Each profile shows:\n• Specialization and experience\n• Available time slots\n• Consultation fee",
        ],
        // Profile / account
        [
            'patterns' => ['profile', 'update.*info', 'change.*password', 'password', 'account.*setting', 'personal.*info'],
            'answer'   => "You can update your personal details and change your password from the **Profile** page — click your name in the top navigation bar.",
        ],
        // Doctor chat
        [
            'patterns' => ['message.*doctor', 'chat.*doctor', 'contact.*doctor', 'talk.*doctor', 'send.*message'],
            'answer'   => "You can message your doctor directly from your **Dashboard**. Click on any appointment row to open the detail panel, then use the Messages section at the bottom.",
        ],
        // Thanks / positive
        [
            'patterns' => ['thank', 'thanks', 'thx', 'great', 'awesome', 'helpful', 'perfect', 'nice'],
            'answer'   => "You are welcome. Is there anything else I can help you with?",
        ],
    ];

    foreach ($faqs as $faq) {
        foreach ($faq['patterns'] as $pattern) {
            if (preg_match('/' . $pattern . '/i', $msg)) {
                return $faq['answer'];
            }
        }
    }

    return null;
}


// ── Groq fallback ────────────────────────────────────────────────────
// Returns:
//   string         — real answer to show patient
//   'ESCALATE'     — DocBook question Groq cannot answer → flag admin
//   'OUT_OF_SCOPE' — off-topic → decline politely, no escalation
//   null           — API/network error → escalate as safety fallback

function chatbot_groq_fallback(string $userMessage, array $history): ?string
{
    $apiKey = defined('GROQ_API_KEY') ? GROQ_API_KEY : (getenv('GROQ_API_KEY') ?: '');

    if ($apiKey === '') {
        return null;
    }

    $messages = [
        [
            'role'    => 'system',
            'content' =>
                "You are the DocBook virtual assistant — a helpful, concise chatbot for a doctor appointment booking platform in Nepal. "
                . "Always refer to yourself as 'DocBook Assistant'. Never mention Groq, Llama, or any AI model name.\n\n"
                . "DocBook allows patients to:\n"
                . "- Browse doctors by specialization\n"
                . "- Book, reschedule, or cancel appointments\n"
                . "- Pay via eSewa (the only supported payment method)\n"
                . "- Message their doctor through the dashboard\n"
                . "- Update their profile and password\n\n"
                . "STRICT RULES:\n"
                . "1. Only answer questions about DocBook features, appointments, payments, or account management.\n"
                . "2. Never give medical advice, diagnoses, or drug recommendations.\n"
                . "3. Never invent platform features that do not exist.\n"
                . "4. Do not use emojis in any response.\n"
                . "5. If the question is related to DocBook but you genuinely cannot answer it, "
                . "reply with ONLY the single word ESCALATE — no other text, no punctuation, nothing else.\n"
                . "6. If the question is completely unrelated to DocBook (weather, general knowledge, "
                . "jokes, or anything not about this platform), "
                . "reply with ONLY the single word OUT_OF_SCOPE — no other text, no punctuation, nothing else.\n"
                . "7. Keep answers short — 3 to 5 lines max. Use bullet points (•) when listing steps. Never use dashes (- or —) as list markers.\n"
                . "8. Be professional. Do not use emojis.\n"
                . "9. Only mention eSewa as the payment provider.",
        ],
    ];

    $recent = array_slice($history, -6);
    foreach ($recent as $turn) {
        $messages[] = [
            'role'    => $turn['role'] === 'user' ? 'user' : 'assistant',
            'content' => $turn['message'],
        ];
    }

    $lastHistoryMsg = end($recent);
    if (!$lastHistoryMsg || $lastHistoryMsg['role'] !== 'user' || $lastHistoryMsg['message'] !== $userMessage) {
        $messages[] = ['role' => 'user', 'content' => $userMessage];
    }

    $payload = json_encode([
        'model'       => 'meta-llama/llama-4-scout-17b-16e-instruct',
        'messages'    => $messages,
        'max_tokens'  => 300,
        'temperature' => 0.3,
    ]);

    $context  = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/json\r\nAuthorization: Bearer " . $apiKey,
            'content'       => $payload,
            'timeout'       => 10,
            'ignore_errors' => true,
        ],
    ]);

    $raw      = @file_get_contents('https://api.groq.com/openai/v1/chat/completions', false, $context);
    $httpCode = 0;
    if (isset($http_response_header)) {
        preg_match('/HTTP\/\S+ (\d+)/', $http_response_header[0], $m);
        $httpCode = (int)($m[1] ?? 0);
    }

    if ($raw === false || $httpCode !== 200) {
        return null;
    }

    $data  = json_decode($raw, true);
    $reply = trim($data['choices'][0]['message']['content'] ?? '');

    if ($reply === '') return null;

    // Groq sometimes prefixes the sentinel then continues with a real answer.
    // Strip the sentinel word from the start and return whatever follows.
    $stripped = preg_replace('/^(ESCALATE|OUT_OF_SCOPE)[.\s]*/i', '', $reply);

    $firstWord = strtoupper(preg_replace('/[\s\W]+/', '', explode(' ', trim($reply))[0]));

    if ($firstWord === 'OUTOFSCOPE') {
        // Truly off-topic — return nothing so caller shows the decline message
        return 'OUT_OF_SCOPE';
    }

    if ($firstWord === 'ESCALATE') {
        // Had an answer after the sentinel — return it instead of flagging admin
        $remaining = trim($stripped);
        return $remaining !== '' ? $remaining : 'ESCALATE';
    }

    return $reply;
}


// ── API: POST /api/chatbot/message ───────────────────────────────────

function api_chatbot_message(): void
{
    require_auth_api();

    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $message = trim($body['message'] ?? '');
    $history = $body['history'] ?? [];

    if ($message === '') {
        json_response(['success' => false, 'message' => 'Empty message.'], 422);
    }

    // Tier 1: hardcoded FAQ
    $faqAnswer = chatbot_faq_match($message);
    if ($faqAnswer !== null) {
        json_response(['success' => true, 'answer' => $faqAnswer, 'escalated' => false, 'source' => 'faq']);
    }

    // Tier 2: Groq
    $groqResult = chatbot_groq_fallback($message, $history);

    if ($groqResult === 'OUT_OF_SCOPE') {
        json_response([
            'success'   => true,
            'answer'    => "I can only help with DocBook-related questions such as booking, cancelling, or rescheduling appointments, payments, or your account. Is there anything like that I can help with?",
            'escalated' => false,
            'source'    => 'out_of_scope',
        ]);
    }

    if ($groqResult !== null && $groqResult !== 'ESCALATE') {
        json_response(['success' => true, 'answer' => $groqResult, 'escalated' => false, 'source' => 'groq']);
    }

    // Tier 3: escalate to admin (only genuine DocBook questions Groq couldn't answer)
    json_response([
        'success'   => true,
        'answer'    => "I could not find a good answer to that. I have flagged your message and an admin will follow up with you shortly.",
        'escalated' => true,
        'source'    => 'escalated',
    ]);
}


// ── API: POST /api/chatbot/escalate ──────────────────────────────────

function api_chatbot_escalate(): void
{
    $user       = require_auth_api();
    $patient_id = (int) $user['id'];

    $body         = json_decode(file_get_contents('php://input'), true) ?? [];
    $user_query   = trim($body['user_query']   ?? '');
    $conversation = $body['conversation']       ?? [];

    if ($user_query === '') {
        json_response(['success' => false, 'message' => 'Missing query.'], 422);
    }

    $pdo  = db_connect();
    $stmt = $pdo->prepare(
        "INSERT INTO chatbot_escalations
            (patient_id, patient_name, user_query, conversation)
         VALUES
            (:pid, :pname, :query, :conv)"
    );
    $stmt->execute([
        ':pid'   => $patient_id,
        ':pname' => $user['name'] ?? '',
        ':query' => $user_query,
        ':conv'  => json_encode($conversation),
    ]);

    json_response(['success' => true]);
}


// ── API: GET /admin/api/chatbot/escalations ──────────────────────────

function api_admin_chatbot_escalations(): void
{
    require_admin_api();

    $status = trim($_GET['status'] ?? '');
    $pdo    = db_connect();

    $where  = '';
    $params = [];
    if (in_array($status, ['open', 'resolved', 'dismissed'], true)) {
        $where             = 'WHERE e.status = :status';
        $params[':status'] = $status;
    }

    $stmt = $pdo->prepare(
        "SELECT e.id, e.patient_id, e.patient_name, e.user_query,
                e.conversation, e.status, e.admin_note, e.created_at, e.updated_at
         FROM chatbot_escalations e
         {$where}
         ORDER BY
             CASE e.status WHEN 'open' THEN 0 WHEN 'resolved' THEN 1 ELSE 2 END,
             e.created_at DESC
         LIMIT 300"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $row['conversation'] = json_decode($row['conversation'], true) ?? [];
    }
    unset($row);

    $counts = $pdo->query(
        "SELECT status, COUNT(*) AS cnt FROM chatbot_escalations GROUP BY status"
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    json_response([
        'success'     => true,
        'escalations' => $rows,
        'counts'      => [
            'open'      => (int)($counts['open']      ?? 0),
            'resolved'  => (int)($counts['resolved']  ?? 0),
            'dismissed' => (int)($counts['dismissed'] ?? 0),
        ],
    ]);
}


// ── API: PATCH /admin/api/chatbot/escalations/{id} ───────────────────

function api_admin_chatbot_update(int $id): void
{
    require_admin_api();

    $body       = json_decode(file_get_contents('php://input'), true) ?? [];
    $new_status = trim($body['status']     ?? '');
    $admin_note = trim($body['admin_note'] ?? '');

    $allowed = ['open', 'resolved', 'dismissed'];
    if ($new_status !== '' && !in_array($new_status, $allowed, true)) {
        json_response(['success' => false, 'message' => 'Invalid status.'], 422);
    }

    $pdo    = db_connect();
    $sets   = [];
    $params = [':id' => $id];

    if ($new_status !== '') { $sets[] = 'status = :status';     $params[':status'] = $new_status; }
    if ($admin_note !== '') { $sets[] = 'admin_note = :note';   $params[':note']   = $admin_note; }

    if (empty($sets)) {
        json_response(['success' => false, 'message' => 'Nothing to update.'], 422);
    }

    $pdo->prepare(
        "UPDATE chatbot_escalations SET " . implode(', ', $sets) . " WHERE id = :id"
    )->execute($params);

    json_response(['success' => true]);
}


// Page: GET /admin/chatbot-escalations

function admin_chatbot_escalations_page(): void
{
    $user = require_admin();
    render('admin/chatbot-escalations', compact('user'));
}

endif;