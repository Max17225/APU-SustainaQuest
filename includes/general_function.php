<?php

// Function to get image paths based on a param
// param: string $key - the key representing the image(like 'logo')
// return: string - the corresponding image path
function image_path(string $key): string
{
    $map = [
        "logo" => "../assets/image/SustainaQuest Logo.png"
    ];

    return $map[$key] ?? $map['placeholder'];
}

