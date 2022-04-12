CREATE TABLE ocsensor_timeline (
    timeline_id integer NOT NULL,
    post_id integer,
    executor_id integer,
    group_id_in_charge integer,
    target_operator_id integer,
    target_group_id integer,
    action varchar(255),
    status_at_end varchar(255),
    start_at timestamp,
    end_at timestamp,
    duration integer,
    post_parent_category_id integer,
    post_child_category_id integer,
    post_area_id integer,
    post_author_id integer,
    post_author_group_id integer,
    post_status varchar(50),
    post_type varchar(255)
);
ALTER TABLE ONLY ocsensor_timeline ADD CONSTRAINT ocsensor_timeline_pkey PRIMARY KEY (timeline_id, post_id);
CREATE INDEX ocsensor_timeline_status_start_idx ON ocsensor_timeline USING btree (post_status, start_at DESC);
CREATE INDEX ocsensor_timeline_duration_group_id_idx ON ocsensor_timeline USING btree (duration, group_id_in_charge);
CREATE INDEX ocsensor_timeline_timeline_post_idx ON ocsensor_timeline USING btree (timeline_id, post_id);
CREATE INDEX ocsensor_timeline_post_id ON ocsensor_timeline USING btree (post_id);
CREATE INDEX ocsensor_timeline_status ON ocsensor_timeline USING btree (post_status);
CREATE INDEX ocsensor_timeline_post_id_end_at ON ocsensor_timeline USING btree (post_id, end_at);
