-- PLACE HERE 'ALTER TABLE' COMMANDS TO DROP FOREIGN KEYS.
-- PLACE HERE 'DROP TABLE IF EXISTS' COMMANDS.

-- CREATE TABLES.
CREATE TABLE USER(
    user_id int NOT NULL AUTO_INCREMENT,
    user_email varchar(75) NOT NULL,
    user_password varchar(1000) NOT NULL,
    user_type int NOT NULL,
    PRIMARY KEY (user_id)
) Engine=InnoDB;

CREATE TABLE USER_TYPE(
    type_id int NOT NULL,
    type_description varchar(25),
    PRIMARY KEY (type_id)
) Engine=InnoDB;

CREATE TABLE COURSE(
    course_id int NOT NULL AUTO_INCREMENT,
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
    section_parent_id int,
    section_name varchar(150),
    PRIMARY KEY (section_id)
) Engine=InnoDB;

CREATE TABLE ITEM(
    item_id int NOT NULL AUTO_INCREMENT,
    section_id int NOT NULL,
    item_name varchar(50),
    file_path varchar(200),
    PRIMARY KEY (item_id)
) Engine=InnoDB;

CREATE TABLE USER_COURSE(
    enrolled_course_id int NOT NULL AUTO_INCREMENT,
    user_id int NOT NULL,
    course_id int NOT NULL,
    PRIMARY KEY (enrolled_course_id)
) Engine=InnoDB;

CREATE TABLE ASSESSMENT(
    assessment_id int NOT NULL AUTO_INCREMENT,
    assessment_description varchar(4000),
    assessment_type int NOT NULL,
    points_possible int,
    score_type int,
    due_date date NOT NULL,
    PRIMARY KEY (assessment_id)
) Engine=InnoDB;

CREATE TABLE USER_ASSESSMENT(
    user_assessment_id int NOT NULL AUTO_INCREMENT,
    user_id int NOT NULL,
    course_id int NOT NULL,
    assessment_id int NOT NULL
    assessment_score int,
    assessment_score_detail varchar(20),
    PRIMARY KEY (user_assessment_id)
) Engine=InnoDB;

CREATE TABLE ASSESSMENT_TYPE(
    assessment_type_id int NOT NULL,
    type_description varchar(25) NOT NULL,
    assessment_weight int,
    PRIMARY KEY (assessment_type_id)
) Engine=InnoDB;

CREATE TABLE SCORE_TYPE(
    score_id int NOT NULL AUTO_INCREMENT,
    score_description varchar(50),
    PRIMARY KEY (score_id)
) Engine=InnoDB;

CREATE TABLE GRADE(
    grade_id int NOT NULL AUTO_INCREMENT,
    upper_limit int,
    lower_limit int,
    grade_description varchar(20) NOT NULL,
    result int, -- Will be NULL if assessment has not been graded yet.
    assessment_id int NOT NULL,
    PRIMARY KEY (weight_id)
) Engine=InnoDB;

CREATE TABLE GRADE_RESULT(
    grade_result_id int NOT NULL,
    result_description varchar(10),
    PRIMARY KEY (grade_result_id)
) Engine=InnoDB;

-- FOREIGN KEY RESTRAINTS.

-- ADD VALUES TO TABLES.
INSERT INTO USER_TYPE VALUES(0, "Administrator");
INSERT INTO USER_TYPE VALUES(1, "Instructor");
INSERT INTO USER_TYPE VALUES(2, "Assistant");
INSERT INTO USER_TYPE VALUES(3, "Student");

INSERT INTO ASSESSMENT_TYPE(0, "Extra Credit");
INSERT INTO ASSESSMENT_TYPE(1, "Attendance");
INSERT INTO ASSESSMENT_TYPE(2, "Participation");
INSERT INTO ASSESSMENT_TYPE(3, "Quiz");
INSERT INTO ASSESSMENT_TYPE(4, "Exam");
INSERT INTO ASSESSMENT_TYPE(5, "Lab");
INSERT INTO ASSESSMENT_TYPE(6, "Project");

INSERT INTO SCORE_TYPE(0, "Pass/Fail");
INSERT INTO SCORE_TYPE(1, "Percentile");

INSERT INTO GRADE_RESULT(0, "Fail");
INSERT INTO GRADE_RESULT(1, "Pass");
INSERT INTO GRADE_RESULT(2, "Incomplete");