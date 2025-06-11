<?php

declare(strict_types=1);

echo "\n=== CHAT APPLICATION API DEMO ===\n";
echo "Base URL: http://localhost:8080/api\n\n";

$baseUrl = 'http://localhost:8080/api';

function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

function printResponse($title, $response) {
    echo "📋 $title\n";
    echo "Status: {$response['code']}\n";
    echo "Response: " . json_encode($response['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo str_repeat('-', 80) . "\n";
}

// 1. List all users
$response = makeRequest("$baseUrl/users");
printResponse("List All Users", $response);

// 2. Get specific user
$response = makeRequest("$baseUrl/users/1");
printResponse("Get User Details (Alice)", $response);

// 3. List all groups
$response = makeRequest("$baseUrl/groups");
printResponse("List All Groups", $response);

// 4. Get Development Team messages
$response = makeRequest("$baseUrl/messages/group/1");
printResponse("Development Team Messages", $response);

// 5. Get Design Squad messages
$response = makeRequest("$baseUrl/messages/group/3");
printResponse("Design Squad Messages", $response);

// 6. Create a new user
$response = makeRequest("$baseUrl/users", 'POST', ['username' => 'test_user_demo']);
printResponse("Create New User", $response);

// 7. Try to create a user with existing username (should fail)
$response = makeRequest("$baseUrl/users", 'POST', ['username' => 'alice_smith']);
printResponse("Create User with Existing Username (Should Fail)", $response);

// 8. Create a new group
$response = makeRequest("$baseUrl/groups", 'POST', [
    'name' => 'Demo Group',
    'user_id' => 1
]);
printResponse("Create New Group", $response);

// 9. Join the new group
if (isset($response['data']['id'])) {
    $groupId = $response['data']['id'];
    $response = makeRequest("$baseUrl/groups/$groupId/join", 'POST', ['user_id' => 2]);
    printResponse("Join Group (Bob joins Demo Group)", $response);
    
    // 10. Send a message to the new group
    $response = makeRequest("$baseUrl/messages/group/$groupId", 'POST', [
        'user_id' => 1,
        'content' => 'Welcome to our demo group! This is a test message.'
    ]);
    printResponse("Send Message to Demo Group", $response);
    
    // 11. Leave the group
    $response = makeRequest("$baseUrl/groups/$groupId/leave", 'POST', ['user_id' => 2]);
    printResponse("Leave Group (Bob leaves Demo Group)", $response);
    
    // 12. Try to leave as group creator (should fail)
    $response = makeRequest("$baseUrl/groups/$groupId/leave", 'POST', ['user_id' => 1]);
    printResponse("Group Creator Tries to Leave (Should Fail)", $response);
}

echo "\n=== DEMO COMPLETE ===\n";
echo "🎉 All major API endpoints have been demonstrated!\n";
echo "📊 Database contains realistic sample data for testing.\n";
echo "🏗️ Clean Architecture principles implemented throughout.\n\n"; 