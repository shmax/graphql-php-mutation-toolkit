<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ValidatedFieldDefinition;

try {
    $mutationType = new ObjectType([
        'name' => 'Mutation',
        'fields' => [
            'savePhoneNumbers' => new ValidatedFieldDefinition([
                'name' => 'savePhoneNumbers',
                'type' => Type::boolean(),
                'args' => [
                    'phoneNumbers' => [
                        'errorCodes' => [
                            'requiredValue'
                        ],
                        'validate' => function(array $phoneNumbers) {
                            if (count($phoneNumbers) == 0) {
                                return ['requiredValue', "You must enter at least one phone number"];
                            }
                        },
                        'suberrorCodes' => [
                            'invalidPhoneNumber'
                        ],
                        'validateItem' => function(string $phoneNumber) {
                            $res = preg_match('/^[0-9\-]+$/', $phoneNumber) === 1;
                            return ! $res ? ['invalidPhoneNumber', 'That does not seem to be a valid phone number'] : 0;
                        },
                        'type' => Type::listOf(Type::string())
                    ]
                ],
                'resolve' => function ($value, $args) {
                    // PhoneNumberProvider::setPhoneNumbers($args['phoneNumbers']);
                    return true;
                },
            ]),
        ],
    ]);

    $queryType = new ObjectType([
        'name'=>'Query',
        'fields'=> [
            'phoneNumbers' => Type::listOf(Type::string())
        ]
    ]);

    $schema = new Schema([
        'mutation' => $mutationType,
        'query' => $queryType
    ]);

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $query = $input['query'];
    $variableValues = isset($input['variables']) ? $input['variables'] : null;
    $result = GraphQL::executeQuery($schema, $query, null, null, $variableValues);
    $output = $result->toArray();
} catch (\Exception $e) {
    $output = [
        'error' => [
            'message' => $e->getMessage()
        ]
    ];
}
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($output);
