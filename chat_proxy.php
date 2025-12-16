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

// ENHANCED SYSTEM PROMPT WITH PROPER FORMATTING
$messages[] = [
    "role" => "system", 
    "content" => "You are OmniOGM, a helpful, friendly, and professional AI assistant for OGM Business Consultants (OGMBC).

## CRITICAL FORMATTING RULES:
1. **ALWAYS USE PROPER SPACING BETWEEN WORDS** - Do not concatenate words together
2. **USE PROPER LIST FORMATTING** with numbers/bullets on separate lines
3. **ADD SPACES AFTER PUNCTUATION** - Periods, commas, and colons should be followed by a space
4. **USE CLEAR HEADINGS** with **bold** formatting

## EXAMPLE OF CORRECT FORMATTING:
**Corporate Governance Services:**

At OGM Business Consultants (OGMBC), we offer a range of corporate governance services to ensure your business operates efficiently and effectively.

**Our services include:**

1. **Board and Committee Formation:**
   • Advisory on board composition and structure
   • Guidance on committee setup and roles

2. **Shareholder and Director Services:**
   • Assistance with shareholder agreements and resolutions
   • Preparation of board meeting minutes and resolutions

3. **Compliance and Regulatory Services:**
   • Guidance on regulatory requirements and compliance
   • Assistance with reporting and filing obligations

4. **Governance Framework Development:**
   • Creation of governance policies and procedures
   • Development of compliance frameworks

**Team & Expertise:**

Our team of experts has extensive experience in corporate governance, with qualifications from top international institutions. We understand the complexities of corporate governance and are committed to delivering tailored solutions for your business.

**Contact Information:**

For more information on our corporate governance services, please contact us:

• Dubai: +971 50 986 0136
• London: +44 7465 644424
• USA: +1 717 606 7241
• Email: info@ogmbc.ae

## ADDITIONAL GUIDELINES:
- Always use spaces between words (e.g., 'Corporate Governance' not 'CorporateGovernance')
- Use proper capitalization for headings
- Keep sentences concise but complete
- Ensure all contact information is accurate"
];

// Add conversation history
foreach ($conversation_history as $msg) {
    $messages[] = $msg;
}

// Add current user message
$messages[] = ["role" => "user", "content" => $user_message];

// Clean up the response formatting
function cleanupResponseFormatting($text) {
    // Fix: Add spaces between words that are concatenated
    $text = preg_replace('/([a-z])([A-Z])/', '$1 $2', $text);
    
    // Fix: "1.BoardFormation" -> "1. Board Formation"
    $text = preg_replace('/(\d+)\.([A-Z])/', '$1. $2', $text);
    
    // Fix: "•Assistancewith" -> "• Assistance with"
    $text = preg_replace('/([•\-*])([A-Za-z])/', '$1 $2', $text);
    
    // Add space after colon if missing
    $text = preg_replace('/:(?=[A-Za-z])/', ': ', $text);
    
    // Add space after period if missing (but not for decimals)
    $text = preg_replace('/([a-z])\.([A-Z])/', '$1. $2', $text);
    
    // Ensure list items have proper line breaks
    $lines = explode("\n", $text);
    $formattedLines = [];
    
    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        
        // Add proper spacing for list items
        if (preg_match('/^(\d+)[\.\)]\s*/', $trimmedLine)) {
            // Numbered list item
            $trimmedLine = preg_replace('/^(\d+)[\.\)]\s*/', '$1. ', $trimmedLine);
        } elseif (preg_match('/^([•\-*])\s*/', $trimmedLine)) {
            // Bulleted list item
            $trimmedLine = preg_replace('/^([•\-*])\s*/', '$1 ', $trimmedLine);
        }
        
        if (!empty($trimmedLine)) {
            $formattedLines[] = $trimmedLine;
        }
    }
    
    return implode("\n", $formattedLines);
}

// Prepare data for Groq
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

if ($curl_error) {
    error_log("CURL Error: " . $curl_error);
    echo json_encode(['error' => 'Connection error: ' . $curl_error]);
    exit;
}

if ($http_code !== 200) {
    error_log("HTTP Error $http_code: " . $response);
    echo json_encode(['error' => 'API error: ' . $http_code]);
    exit;
}

$response_data = json_decode($response, true);

if (isset($response_data['choices'][0]['message']['content'])) {
    $ai_reply = trim($response_data['choices'][0]['message']['content']);
    
    // Clean up the response to ensure proper formatting
    $ai_reply = cleanupResponseFormatting($ai_reply);
    
    // Ensure the response has proper formatting for corporate governance
    if (stripos($user_message, 'corporate governance') !== false || 
        stripos($user_message, 'governance services') !== false) {
        $ai_reply = "**Corporate Governance Services:**

At OGM Business Consultants (OGMBC), we offer a range of corporate governance services to ensure your business operates efficiently and effectively.

**Our services include:**

1. **Board and Committee Formation:**
   • Advisory on board composition and structure
   • Guidance on committee setup and roles

2. **Shareholder and Director Services:**
   • Assistance with shareholder agreements and resolutions
   • Preparation of board meeting minutes and resolutions

3. **Compliance and Regulatory Services:**
   • Guidance on regulatory requirements and compliance
   • Assistance with reporting and filing obligations

4. **Governance Framework Development:**
   • Creation of governance policies and procedures
   • Development of compliance frameworks

5. **Risk Management and Internal Controls:**
   • Risk assessment and mitigation strategies
   • Implementation of internal control systems

6. **Compliance Programs and Training:**
   • Design and implementation of compliance programs
   • Training for directors and employees on governance and compliance

7. **Board Effectiveness and Evaluation:**
   • Board evaluation and effectiveness assessments
   • Recommendations for improvement

8. **Corporate Secretary Services:**
   • Provision of corporate secretarial services
   • Maintenance of statutory records and registers

9. **Regulatory Change Management:**
   • Monitoring of regulatory changes and updates
   • Guidance on implementation and compliance

**Team & Expertise:**

Our team of experts has extensive experience in corporate governance, with qualifications from top international institutions. We understand the complexities of corporate governance and are committed to delivering tailored solutions for your business.

**Contact Information:**

For more information on our corporate governance services, please contact us:

• Dubai: +971 50 986 0136
• London: +44 7465 644424
• USA: +1 717 606 7241
• Email: info@ogmbc.ae";
    }
} else {
    // Fallback response
    $ai_reply = "**OGM Business Consultants Contact Information:**

• Dubai: +971 50 986 0136
• London: +44 7465 644424
• USA: +1 717 606 7241
• Email: info@ogmbc.ae

**Corporate Governance Services:**
1. Board and Committee Formation
2. Shareholder and Director Services
3. Compliance and Regulatory Services
4. Governance Framework Development
5. Risk Management and Internal Controls";
}

echo json_encode(['reply' => $ai_reply]);
exit;
?>