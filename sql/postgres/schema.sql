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
    post_type varchar(255),
    is_internal integer
);
ALTER TABLE ONLY ocsensor_timeline ADD CONSTRAINT ocsensor_timeline_pkey PRIMARY KEY (timeline_id, post_id);
CREATE INDEX IF NOT EXISTS ocsensor_timeline_status_start_idx ON ocsensor_timeline USING btree (post_status, start_at DESC);
CREATE INDEX IF NOT EXISTS ocsensor_timeline_duration_group_id_idx ON ocsensor_timeline USING btree (duration, group_id_in_charge);
CREATE INDEX IF NOT EXISTS ocsensor_timeline_timeline_post_idx ON ocsensor_timeline USING btree (timeline_id, post_id);
CREATE INDEX IF NOT EXISTS ocsensor_timeline_post_id ON ocsensor_timeline USING btree (post_id);
CREATE INDEX IF NOT EXISTS ocsensor_timeline_status ON ocsensor_timeline USING btree (post_status);
CREATE INDEX IF NOT EXISTS ocsensor_timeline_post_id_end_at ON ocsensor_timeline USING btree (post_id, end_at);

CREATE TABLE ocsensor_category (
    id integer NOT NULL,
    name varchar(255),
    parent_id integer
);
ALTER TABLE ONLY ocsensor_category ADD CONSTRAINT ocsensor_category_pkey PRIMARY KEY (id);

CREATE TABLE ocsensor_area (
   id integer NOT NULL,
   name varchar(255)
);
ALTER TABLE ONLY ocsensor_area ADD CONSTRAINT ocsensor_area_pkey PRIMARY KEY (id);

CREATE TABLE ocsensor_group (
   id integer NOT NULL,
   name varchar(255),
   tag text,
   reference text
);
ALTER TABLE ONLY ocsensor_group ADD CONSTRAINT ocsensor_group_pkey PRIMARY KEY (id);
CREATE INDEX IF NOT EXISTS ocsensor_group_tag ON ocsensor_group USING btree (tag);
CREATE INDEX IF NOT EXISTS ocsensor_group_reference ON ocsensor_group USING btree (reference);

CREATE TABLE ocsensor_operator (
   id integer NOT NULL,
   name varchar(255)
);
ALTER TABLE ONLY ocsensor_operator ADD CONSTRAINT ocsensor_operator_pkey PRIMARY KEY (id);

--CREATE TABLE ocsensor_operator_group (
--    operator_id integer NOT NULL,
--    group_id integer NOT NULL
--);
--ALTER TABLE ONLY ocsensor_operator_group ADD CONSTRAINT ocsensor_operator_group_pkey PRIMARY KEY (operator_id, group_id);