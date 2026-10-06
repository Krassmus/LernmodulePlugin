<?php

namespace LernmodulePlugin\models;
enum TravisGoPostType: string {
    case Meta = 'meta';
    case Image = 'image';
    case Audio = 'audio';
    case Text = 'text';
}
