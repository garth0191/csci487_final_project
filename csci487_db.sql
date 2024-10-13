ALTER TABLE ITEM DROP FOREIGN KEY ITEM_SECTION;
ALTER TABLE ITEM DROP FOREIGN KEY ITEM_USER;
ALTER TABLE SECTION DROP FOREIGN KEY SECTION_COURSE;
ALTER TABLE USER_ASSESSMENT DROP FOREIGN KEY U_A_ASSESSMENT;
ALTER TABLE USER_ASSESSMENT DROP FOREIGN KEY U_A_COURSE;
ALTER TABLE USER_ASSESSMENT DROP FOREIGN KEY U_A_USER;
ALTER TABLE ASSESSMENT DROP FOREIGN KEY ASSESS_COURSE;
ALTER TABLE USER_COURSE DROP FOREIGN KEY U_C_COURSE;
ALTER TABLE USER_COURSE DROP FOREIGN KEY U_C_USER;
ALTER TABLE COURSE DROP FOREIGN KEY COURSE_INSTRUCTOR;
ALTER TABLE COURSE DROP FOREIGN KEY COURSE_ASSISTANT;

DROP TABLE IF EXISTS ITEM;
DROP TABLE IF EXISTS USER_ASSESSMENT;
DROP TABLE IF EXISTS USER_COURSE;
DROP TABLE IF EXISTS SECTION;
DROP TABLE IF EXISTS COURSE;
DROP TABLE IF EXISTS USER;
DROP TABLE IF EXISTS USER_TYPE;
DROP TABLE IF EXISTS ASSESSMENT;
DROP TABLE IF EXISTS ASSESSMENT_TYPE;


-- CREATE TABLES.
CREATE TABLE USER(
    user_id int NOT NULL AUTO_INCREMENT,
    user_email varchar(75) NOT NULL,
    user_password varchar(1000) NOT NULL,
    user_type int NOT NULL,
    first_name varchar(100),
    last_name varchar(100),
    PRIMARY KEY (user_id)
) Engine=InnoDB;

CREATE TABLE USER_TYPE(
    type_id int NOT NULL,
    type_description varchar(50),
    PRIMARY KEY (type_id)
) Engine=InnoDB;

CREATE TABLE COURSE(
    course_id int NOT NULL AUTO_INCREMENT,
    course_num varchar(50) NOT NULL,
    course_name varchar(150) NOT NULL,
    instructor_id int NOT NULL,
    assistant_id int,
    course_description varchar(4000),
    professor_name varchar(50),
    PRIMARY KEY (course_id)
) Engine=InnoDB;

CREATE TABLE SECTION(
    section_id int NOT NULL AUTO_INCREMENT,
    course_id int NOT NULL,
    section_name varchar(150),
    PRIMARY KEY (section_id)
) Engine=InnoDB;

CREATE TABLE ITEM(
    item_id int NOT NULL AUTO_INCREMENT,
    section_id int NOT NULL,
    user_id int NOT NULL,
    item_name varchar(50),
    file_path varchar(200),
    upload_date date,
    PRIMARY KEY (item_id)
) Engine=InnoDB;

CREATE TABLE USER_COURSE( -- Should be used for student and assistant users ONLY.
    enrolled_course_id int NOT NULL AUTO_INCREMENT,
    user_id int NOT NULL,
    course_id int NOT NULL,
    PRIMARY KEY (enrolled_course_id)
) Engine=InnoDB;

CREATE TABLE ASSESSMENT(
    assessment_id int NOT NULL AUTO_INCREMENT,
    course_id int NOT NULL,
    assessment_description varchar(4000),
    assessment_type int NOT NULL,
    points_possible int,
    due_date date NOT NULL,
    has_submissions int NOT NULL, /* 0 for no, 1 for yes. */
    PRIMARY KEY (assessment_id)
) Engine=InnoDB;

CREATE TABLE USER_ASSESSMENT( /* For student records only -- one per student, per assessment. */
    user_assessment_id int NOT NULL AUTO_INCREMENT,
    user_id int NOT NULL,
    course_id int NOT NULL,
    assessment_id int NOT NULL,
    assessment_score int, /* For PASS/FAIL: 1 or 0, respectively. */
    user_submission_filepath varchar(200),
    PRIMARY KEY (user_assessment_id)
) Engine=InnoDB;

CREATE TABLE ASSESSMENT_TYPE(
    assessment_type_id int NOT NULL,
    type_description varchar(25) NOT NULL,
    assessment_weight int, /* Will be added when the instructor specifies weights on course_edit page. */
    PRIMARY KEY (assessment_type_id)
) Engine=InnoDB;

-- FOREIGN KEY RESTRAINTS.
ALTER TABLE COURSE ADD CONSTRAINT COURSE_INSTRUCTOR FOREIGN KEY (instructor_id)
    REFERENCES USER (user_id) ON DELETE CASCADE;
ALTER TABLE COURSE ADD CONSTRAINT COURSE_ASSISTANT FOREIGN KEY (assistant_id)
    REFERENCES USER (user_id) ON DELETE SET NULL;

ALTER TABLE SECTION ADD CONSTRAINT SECTION_COURSE FOREIGN KEY (course_id)
    REFERENCES COURSE (course_id) ON DELETE CASCADE;

ALTER TABLE ITEM ADD CONSTRAINT ITEM_SECTION FOREIGN KEY (section_id)
    REFERENCES SECTION (section_id) ON DELETE CASCADE;
ALTER TABLE ITEM ADD CONSTRAINT ITEM_USER FOREIGN KEY (user_id)
    REFERENCES USER (user_id) ON DELETE CASCADE;

ALTER TABLE USER_COURSE ADD CONSTRAINT U_C_USER FOREIGN KEY (user_id)
    REFERENCES USER (user_id) ON DELETE CASCADE;
ALTER TABLE USER_COURSE ADD CONSTRAINT U_C_COURSE FOREIGN KEY (course_id)
    REFERENCES COURSE (course_id) ON DELETE CASCADE;

ALTER TABLE ASSESSMENT ADD CONSTRAINT ASSESS_COURSE FOREIGN KEY (course_id)
    REFERENCES COURSE (course_id) ON DELETE CASCADE;

ALTER TABLE USER_ASSESSMENT ADD CONSTRAINT U_A_USER FOREIGN KEY (user_id)
    REFERENCES USER (user_id) ON DELETE CASCADE;
ALTER TABLE USER_ASSESSMENT ADD CONSTRAINT U_A_COURSE FOREIGN KEY (course_id)
    REFERENCES COURSE (course_id) ON DELETE CASCADE;
ALTER TABLE USER_ASSESSMENT ADD CONSTRAINT U_A_ASSESSMENT FOREIGN KEY (assessment_id)
    REFERENCES ASSESSMENT (assessment_id) ON DELETE CASCADE;

-- ADD VALUES TO TABLES.
INSERT INTO USER_TYPE VALUES(0, 'Administrator');
INSERT INTO USER_TYPE VALUES(1, 'Instructor');
INSERT INTO USER_TYPE VALUES(2, 'Assistant');
INSERT INTO USER_TYPE VALUES(3, 'Student');

INSERT INTO ASSESSMENT_TYPE VALUES(0, 'Extra Credit', NULL);
INSERT INTO ASSESSMENT_TYPE VALUES(1, 'Attendance', NULL);
INSERT INTO ASSESSMENT_TYPE VALUES(2, 'Participation', NULL);
INSERT INTO ASSESSMENT_TYPE VALUES(3, 'Quiz', NULL);
INSERT INTO ASSESSMENT_TYPE VALUES(4, 'Exam', NULL);
INSERT INTO ASSESSMENT_TYPE VALUES(5, 'Lab', NULL);
INSERT INTO ASSESSMENT_TYPE VALUES(6, 'Project', NULL);