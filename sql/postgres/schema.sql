CREATE TABLE ocsensor_timeline (
   timeline_id integer NOT NULL,
   creator_id integer,
   post_id integer,
   user_id integer,
   user_group_id integer,
   operator_id integer,
   group_id integer,
   category_id integer,
   area_id integer,
   execution_type varchar(255),
   status varchar(50),
   post_type varchar(255),
   start_at timestamp,
   end_at timestamp,
   execution_time integer
);
ALTER TABLE ONLY ocsensor_timeline ADD CONSTRAINT ocsensor_timeline_pkey PRIMARY KEY (timeline_id, post_id);
CREATE INDEX ocsensor_timeline_status_start_idx ON ocsensor_timeline USING btree (status, start_at DESC);
CREATE INDEX ocsensor_timeline_execution_time_group_id_idx ON ocsensor_timeline USING btree (execution_time, group_id);
CREATE INDEX ocsensor_timeline_timeline_post_idx ON ocsensor_timeline USING btree (timeline_id, post_id);
CREATE INDEX ocsensor_timeline_post_id ON ocsensor_timeline USING btree (post_id);
CREATE INDEX ocsensor_timeline_status ON ocsensor_timeline USING btree (status);
CREATE INDEX ocsensor_timeline_post_id_end_at ON ocsensor_timeline USING btree (post_id, end_at);
