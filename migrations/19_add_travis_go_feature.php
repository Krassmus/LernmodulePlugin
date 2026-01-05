<?php

class AddTravisGoFeature extends Migration
{
    public function up(): void {
        $db = DBManager::get();

        // Each "Travis Go" Post belongs to an Interactive Video.
        // Interactive Videos are stored as JSON blobs either in lernmodule_module or cw_blocks.
        // When we want to get the Posts under a video, we have to know where the video is stored.
        // Then, we can query the travis_go_posts table:
        // WHERE video_id = :id AND video_type = 'lernmodule_module'
        // -or-
        // WHERE video_id = :id AND video_type = 'cw_blocks'
        $query = "
          create table if not exists lernmodule_travis_go_posts (
            id int(11) unsigned not null auto_increment,
            video_id char(32) not null, /* corresponds to lernmodule_module.module_id or cw_blocks.id */
            video_type varchar(255) not null, /* either lernmodule_module or cw_blocks */
            mk_user_id char(32) not null,
            mkdate int(11) unsigned not null,
            chdate int(11) unsigned not null,
            start_time float unsigned not null,
            end_time float unsigned,
            contents text not null,
            post_type ENUM('meta', 'image', 'audio', 'text') not null default 'meta',
            primary key (id)
        )";
        $db->exec($query);
    }

    public function down(): void {
        $query = "
          drop table if exists
            lernmodule_travis_go_posts
        ";
        DBManager::get()->exec($query);
    }

}
