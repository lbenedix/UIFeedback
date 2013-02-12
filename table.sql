CREATE TABLE /*_*/uifeedback (
  id         INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  type       INT,                     -- 0 Questionnaire, 1 Screenshot
  created    TIMESTAMP DEFAULT NOW(), -- Timestamp
  url        VARCHAR(255),            -- URL where that feedback was given
  task       VARCHAR(255),            --
  done       INT(1),                  -- 0 no, 1 yes, '' undefined
  text1      TEXT,                  -- free text (to be defined)
--  text2      TEXT,
--  text3      TEXT,
--  text4      TEXT,
--  text5      TEXT,
  importance INT,                     -- 0 unknown, 1 critical, 2 serious, 3 cosmetic
  happened   INT,                     -- 0 unknown, 1 not expected, 2 confused, 3 missing feature, 4 other
  username   VARCHAR(255),            -- Username of the reporter or anonymous
  notify     INT(1),                  -- 0 User dont want a notification on status-change, 1 Notify
  useragent  VARCHAR(255),            -- UserAgend (Browser/OS identification)
  screenshot LONGBLOB,                -- Binary data of the Screenshot
  image_size VARCHAR(255),            -- size of the image in KB(?)
  status     int(1),                  -- actual status
  comment    varchar(2000)            -- actual comment
)/*$wgDBTableOptions*/;

CREATE TABLE /*_*/uifeedback_reviews (
  id          INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  feedback_id INT NOT NULL,            -- ID of the FeedbackItem
  created     TIMESTAMP DEFAULT NOW(), -- Timestamp
  reviewer    VARCHAR(256) NOT NULL,   -- username of the reviewer
  status      INT(1) NOT NULL,         -- 0 open, 1 in review, 2 closed, 3 declined
  comment     VARCHAR(2000) NOT NULL   -- comment for actual status, e.g. a reason for rejection or a link to bugzilla-bug
)/*$wgDBTableOptions*/;

CREATE TABLE /*_*/uifeedback_stats (
  type      INT,          -- 0 dynamic request (popup), 1 questionnaire-button, 2 screenshot-button
  shown     INT NOT NULL, -- number of views
  clicked   INT NOT NULL, -- number of clicks
  sent      INT NOT NULL  -- number of sent forms
)/*$wgDBTableOptions*/;

INSERT INTO  /*_*/uifeedback_stats (
  type ,shown, clicked, sent
) VALUES (0,0,0,0);

INSERT INTO  /*_*/uifeedback_stats (
  type ,shown, clicked, sent
) VALUES (1,0,0,0);

INSERT INTO  /*_*/uifeedback_stats (
  type ,shown, clicked, sent
) VALUES (2,0,0,0);