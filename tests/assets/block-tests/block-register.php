<?php

register_block_type('timber/test-block', [
    'render_callback' => 'render_block_example',
    'api_version' => 3,
    'attributes' => [
        'post_id' => [
            'type' => 'integer',
        ],
    ],
]);

function render_block_example($attributes, $content = '', $wp_block = null)
{
    // get the dynamically set post ID from the block attributes. We do it this way because get_the_ID() doesn't work in the block context during phpUnit tests.
    return Timber::compile('block-template.twig', [
        'post' => Timber::get_post($attributes['post_id']),
        'attributes' => $attributes,
    ]);
}
