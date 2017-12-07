<?php

// Test this using following command
// php -S localhost:8080 ./graphql.php &
// curl http://localhost:8080 -d '{"query": "query { echo(message: \"Hello World\") }" }'
// curl http://localhost:8080 -d '{"query": "mutation { sum(x: 2, y: 2) }" }'

require_once __DIR__ . '/vendor/autoload.php';
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ResolveInfo;


try {
    
    $queryType = new ObjectType([
        'name' => 'Query',
        'fields' => [
            'echo' => [
                'type' => Type::string(),
                'args' => [
                    //'message' => ['type' => Type::nonnull(Type::string())],
                    'message' => ['type' => Type::string()],
                ],
                'resolve' => function ($root, $args) {
                    return $root['prefix'] . $args['message'];
                }
            ],
        ]

    ]);

    $mutationType = new ObjectType([
        'name' => 'Calc',
        'fields' => [
            'sum' => [
                'type' => Type::float(),
                'args' => [
                    'x' => [
                        'type' => Type::nonnull(Type::float()),
                        //'type' => Type::float(),
                        'description' => 'Enviar um valor para soma'
                    ],

                    'y' => [
                        'type' => Type::int()
                    ],

                ],
                'resolve' => function ($root, $args) {
                    $x = $args['x'] ?? 0;
                    $y = $args['y'] ?? 0;

                    return $x + $y;
                },
            ],
        ],
    ]);
    // See docs on schema options:
    // http://webonyx.github.io/graphql-php/type-system/schema/#configuration-options
    $schema = new Schema([
        'query' => $queryType,
        'mutation' => $mutationType,
    ]);
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    $query = $input['query'];
    $variableValues = isset($input['variables']) ? $input['variables'] : null;
    $rootValue = ['prefix' => 'You said: '];
    $result = GraphQL::executeQuery($schema, $query, $rootValue, null, $variableValues);
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