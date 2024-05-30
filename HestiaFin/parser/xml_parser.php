<?php
function startElement($parser, $name, $attrs) {
    echo "Start tag: $name\n";
}

function endElement($parser, $name) {
    echo "End tag: $name\n";
}

function characterData($parser, $data) {
    echo "Text: $data\n";
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

$parser = xml_parser_create();

xml_set_element_handler($parser, "startElement", "endElement");
xml_set_character_data_handler($parser, "characterData");

if (!xml_parse($parser, $xml, true)) {
    echo sprintf("XML error: %s at line %d",
        xml_error_string(xml_get_error_code($parser)),
        xml_get_current_line_number($parser)
    );
}

xml_parser_free($parser);