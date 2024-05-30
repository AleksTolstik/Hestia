<?php
function startElement($parser, $name, $attrs) {
    global $currentElement;
    $currentElement = $name;
}

function endElement($parser, $name) {
    global $currentElement;
    $currentElement = null;
}

function characterData($parser, $data) {
    global $currentElement, $users, $currentUser;

    if ($currentElement == "NAME") {
        $currentUser['name'] = $data;
    } elseif ($currentElement == "EMAIL") {
        $currentUser['email'] = $data;
    }
}

function parseXML($xml) {
    global $users, $currentUser;
    $users = [];
    $currentUser = [];

    $parser = xml_parser_create();
    xml_set_element_handler($parser, "startElement", "endElement");
    xml_set_character_data_handler($parser, "characterData");

    xml_parse($parser, $xml);
    xml_parser_free($parser);

    return $users;
}

$xml = <<<XML
<users>
    <user>
        <name>alex</name>
        <email>alex@example.com</email>
    </user>
    <user>
        <name>sofia</name>
        <email>sofia@example.com</email>
    </user>
    <user>
        <name>vitaliy</name>
        <email>vitaliy@example.com</email>
    </user>
</users>
XML;

$users = parseXML($xml);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users List</title>
</head>
<body>
    <h1>Users List</h1>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Email</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
