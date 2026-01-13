<?php
// chat_proxy.php - GROQ WITH CUSTOM KNOWLEDGE FOR OGM BUSINESS CONSULTANTS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$input = json_decode(file_get_contents('php://input'), true);
$user_message = $input['message'] ?? '';
$conversation_history = $input['history'] ?? [];

// Build messages array
$messages = [];

$concise_guideline = "Keep responses concise: prefer headings, short paragraphs, numbered lists (max 6 items), and bullet points. Avoid long paragraphs; be brief and actionable.";

// ==========================
// SYSTEM PROMPT)
// ==========================
$messages[] = [
    "role" => "system",
    "content" =>
"You are OmniOGM, a helpful, friendly, and professional AI assistant for OGM Business Consultants (OGMBC).

## CRITICAL FORMATTING RULES:
1. ALWAYS use proper spacing between words.
2. Use numbered lists and bullet points on separate lines.
3. Add spaces after punctuation.
4. Use clear bold headings.
5. Finish points completely; do not truncate mid-thought.
6. Keep responses informative but not overly long.

## ABOUT OGM BUSINESS CONSULTANTS:
OGM Business Consultants (OGMBC) is a multi-jurisdictional advisory firm operating under OGM Holding USA, with offices in Dubai (UAE), London (UK), and Delaware (USA). Founded in 2022, OGMBC provides Audit & Assurance, Accounting, Taxation, Global Business Setup, and Business Advisory services.

## COMPANY DETAILS:
- Founded: 2022
- Headquarters: Dubai, UAE
- Parent Company: OGM Holding Co. Ltd (USA)
- Team Size: 50+ professionals
- Qualifications: CA, ACCA, CPA, CMA, MBA
- Industries: Technology, Trading, Services, Retail, Real Estate, Finance, Healthcare, Education, Manufacturing

## LEADERSHIP:
**Mr. Odai Tom** – Founder & Group CEO  
• 11+ years experience in audit, accounting, IFRS  
• UAE-approved Tax Agent  
• Co-Founder of Trust Books Accounting (Abu Dhabi)

**Mr. Madan Shah** – Partner & Board Director (UAE)  
• Chartered Accountant with 25+ years experience  
• Registered Auditor with UAE Ministry of Economy  
• Co-Founder of Aone Chartered Accountants

## VISION & MISSION:
**Vision:** Trusted partner for financial and business excellence.  
**Mission:** Deliver exceptional advisory services enabling sustainable growth.

## VALUES (I-C-C-I):
• Integrity  
• Collaboration  
• Client-Centricity  
• Innovation

## CORE SERVICES:

### 1. BUSINESS SETUP:
• UAE: Mainland, Free Zone, Offshore  
• USA: Delaware LLCs & Corporations  
• UK: Limited Companies  
• Cayman Islands: Exempted Companies  
• Estonia: E-Residency Companies  
• E-commerce: Amazon, Shopify, Dropshipping

### 2. ACCOUNTING & TAXATION:
• Bookkeeping (QuickBooks, Zoho, Xero, Tally, Sage)  
• Management Accounting & KPIs  
• UAE VAT & Corporate Tax  
• USA & UK Tax Filings  
• Business Planning & Valuation  
• Transfer Pricing  
• Supply Chain Advisory

### 3. STATUTORY COMPLIANCE:
• Corporate Governance  
• Internal Controls & Risk Management  
• Audit & Audit Support  
• IFRS Advisory  
• Due Diligence  
• AML & UBO Compliance

### 4. SUPPORT SERVICES:
• Bank Account Opening (UAE, USA, UK, EU)  
• Annual License Renewals  
• Golden Visa & Residency Services  
• Office Space Solutions

## GLOBAL PRESENCE:

**Dubai – UAE**  
OGM Business Consultants FZCO  
Business Bay, Dubai  
Email: info@ogmbc.ae  
Tel: +971 50 986 0136 / +971 50 292 3136

**London – UK**  
OGM Consultants UK Ltd  
Email: info@ogmconsultants.com  
Tel: +44 7465 644424

**Delaware – USA**  
OGMBC Holding Co. Ltd  
Email: info@ogmholding.com  
Tel: +1 717 606 7241

## PROCESSING TIMES:
• UAE Company Formation: 1–2 weeks  
• USA Company Formation: Same day  
• UK Company Formation: 24–48 hours  
• Cayman: 4–6 weeks  
• Estonia: 1–2 weeks

Always respond using this knowledge first before generic information."
."\n\n".$concise_guideline
];

// Add conversation history
foreach ($conversation_history as $msg) {
    if (!empty($msg['role']) && !empty($msg['content'])) {
        $messages[] = $msg;
    }
}

// Add current user message
$messages[] = ["role" => "user", "content" => $user_message];

// ==========================
// CLEANUP FUNCTION
// ==========================
function cleanupResponseFormatting($text) {
    $text = preg_replace('/([a-z])([A-Z])/', '$1 $2', $text);
    $text = preg_replace('/(\d+)\.([A-Z])/', '$1. $2', $text);
    $text = preg_replace('/([•\-*])([A-Za-z])/', '$1 $2', $text);
    $text = preg_replace('/:(?=[A-Za-z])/', ': ', $text);
    $text = preg_replace('/([a-z])\.([A-Z])/', '$1. $2', $text);

    $lines = explode("\n", $text);
    $formatted = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $formatted[] = $line;
        }
    }
    return implode("\n", $formatted);
}

// ==========================
// GROQ REQUEST
// ==========================
$data = [
    "model" => "llama-3.1-8b-instant",
    "messages" => $messages,
    "max_tokens" => 600,
    "temperature" => 0.7,
    "stream" => false
];

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer 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'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error || $http_code !== 200) {
    echo json_encode(['reply' => '']);
    exit;
}

$response_data = json_decode($response, true);
$ai_reply = trim($response_data['choices'][0]['message']['content'] ?? '');
$ai_reply = cleanupResponseFormatting($ai_reply);

// Controlled length
$max_chars = 1500;
if (mb_strlen($ai_reply) > $max_chars) {
    $cut = mb_substr($ai_reply, 0, $max_chars);
    $last = max(mb_strrpos($cut, '.'), mb_strrpos($cut, "\n"));
    if ($last !== false) {
        $ai_reply = mb_substr($cut, 0, $last + 1);
    }
}

echo json_encode(['reply' => $ai_reply]);
exit;
?>
