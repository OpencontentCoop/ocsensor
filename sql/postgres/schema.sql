CREATE TABLE statistic_post (
   id integer NOT NULL,
   type TEXT,
   status TEXT,
   workflow TEXT,
   privacy TEXT,
   moderation TEXT,
   coordinates TEXT,
   expire_at timestamp with time zone,
   behalf boolean,
   is_read boolean,
   is_assigned boolean,
   is_fixed boolean,
   is_closed boolean,
   open_at timestamp with time zone,
   read_at timestamp with time zone,
   assigned_at timestamp with time zone,
   fixed_at timestamp with time zone,
   closed_at timestamp with time zone,
   owner_group TEXT,
   area TEXT,
   category TEXT,
   category_detail TEXT,
   reading_duration integer,
   assigning_duration integer,
   fixing_duration integer,
   closing_duration integer,
   bouncing_ball_duration integer
);
ALTER TABLE ONLY statistic_post ADD CONSTRAINT statistic_post_pkey PRIMARY KEY (id);
CREATE INDEX statistic_post_category ON statistic_post USING btree (category);
CREATE INDEX statistic_post_category_detail ON statistic_post USING btree (category_detail);
CREATE INDEX statistic_post_owner_group ON statistic_post USING btree (owner_group);
CREATE INDEX statistic_post_area ON statistic_post USING btree (area);
