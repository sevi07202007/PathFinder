CREATE DATABASE IF NOT EXISTS pathfinder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pathfinder;

CREATE TABLE COURSE (
  Course_ID INT AUTO_INCREMENT PRIMARY KEY,
  Course_Name VARCHAR(150) NOT NULL UNIQUE,
  Description TEXT
) ENGINE=InnoDB;

CREATE TABLE CAREER (
  Career_ID INT AUTO_INCREMENT PRIMARY KEY,
  Course_ID INT NOT NULL,
  Career_Title VARCHAR(150) NOT NULL UNIQUE,
  Description TEXT,
  CONSTRAINT fk_career_course FOREIGN KEY (Course_ID) REFERENCES COURSE(Course_ID)
) ENGINE=InnoDB;

CREATE TABLE ASSESSMENT (
  Assessment_ID INT AUTO_INCREMENT PRIMARY KEY,
  Date_Taken DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE SKILL_INTEREST (
  Item_ID INT AUTO_INCREMENT PRIMARY KEY,
  Item_Name VARCHAR(100) NOT NULL,
  Item_Type VARCHAR(10) NOT NULL,
  CONSTRAINT uq_skill_interest UNIQUE (Item_Name, Item_Type),
  CONSTRAINT chk_item_type CHECK (Item_Type IN ('Skill', 'Interest'))
) ENGINE=InnoDB;

CREATE TABLE RESPONSE (
  Response_ID INT AUTO_INCREMENT PRIMARY KEY,
  Assessment_ID INT NOT NULL,
  Item_ID INT NOT NULL,
  CONSTRAINT uq_response_assessment_item UNIQUE (Assessment_ID, Item_ID),
  CONSTRAINT fk_response_assessment FOREIGN KEY (Assessment_ID) REFERENCES ASSESSMENT(Assessment_ID),
  CONSTRAINT fk_response_item FOREIGN KEY (Item_ID) REFERENCES SKILL_INTEREST(Item_ID)
) ENGINE=InnoDB;

CREATE TABLE CAREER_REQUIREMENT (
  Requirement_ID INT AUTO_INCREMENT PRIMARY KEY,
  Career_ID INT NOT NULL,
  Item_ID INT NOT NULL,
  CONSTRAINT uq_career_requirement UNIQUE (Career_ID, Item_ID),
  CONSTRAINT fk_requirement_career FOREIGN KEY (Career_ID) REFERENCES CAREER(Career_ID),
  CONSTRAINT fk_requirement_item FOREIGN KEY (Item_ID) REFERENCES SKILL_INTEREST(Item_ID)
) ENGINE=InnoDB;

CREATE TABLE RECOMMENDATION (
  Recommendation_ID INT AUTO_INCREMENT PRIMARY KEY,
  Assessment_ID INT NOT NULL,
  Career_ID INT NOT NULL,
  Rank_No INT NOT NULL,
  CONSTRAINT uq_recommendation_assessment_career UNIQUE (Assessment_ID, Career_ID),
  CONSTRAINT chk_rank_number CHECK (Rank_No >= 1),
  CONSTRAINT fk_recommendation_assessment FOREIGN KEY (Assessment_ID) REFERENCES ASSESSMENT(Assessment_ID),
  CONSTRAINT fk_recommendation_career FOREIGN KEY (Career_ID) REFERENCES CAREER(Career_ID)
) ENGINE=InnoDB;

INSERT INTO COURSE (Course_Name, Description) VALUES
('Multimedia Arts, Interaction Design, Psychology, Information Design', 'Study path for UX design.'),
('Computer Science, Information Technology, Software Engineering', 'Study path for software engineering.'),
('Statistics, Economics, Data Science, Business Analytics', 'Study path for data analysis.'),
('Business Administration, Marketing, Management, Computer Science', 'Study path for product management.'),
('Computer Science, Information Security, IT, Network Engineering', 'Study path for cybersecurity.'),
('Communication, Marketing, Journalism, English', 'Study path for content strategy.');

INSERT INTO CAREER (Course_ID, Career_Title, Description) VALUES
(1, 'UX Designer', 'Designs intuitive digital experiences that help people find information, complete tasks, and feel confident using products.'),
(2, 'Software Engineer', 'Builds apps, websites, and systems that solve real-world problems through code, testing, and product thinking.'),
(3, 'Data Analyst', 'Turns numbers into meaningful insight so teams can make smarter decisions with evidence and forecasting.'),
(4, 'Product Manager', 'Connects customer needs, business goals, and engineering execution to shape the roadmap of digital products.'),
(5, 'Cybersecurity Specialist', 'Protects digital systems, data, and users by identifying risks, securing infrastructure, and responding to threats.'),
(6, 'Content Strategist', 'Plans how brands tell stories, build trust, and reach audiences through content, campaigns, and digital channels.');

INSERT INTO SKILL_INTEREST (Item_Name, Item_Type) VALUES
('Solving problems with logic and code', 'Interest'),
('Helping people and understanding their needs', 'Interest'),
('Designing ideas and making them visually appealing', 'Interest'),
('Planning strategy and making decisions', 'Interest'),
('A fast-moving tech workspace with digital tools', 'Interest'),
('A collaborative team where I can communicate and inspire', 'Interest'),
('A creative studio where I can experiment and design', 'Interest'),
('A strategic office where I evaluate numbers and plans', 'Interest'),
('Break it apart and find the root cause', 'Interest'),
('Talk it through and get feedback from others', 'Interest'),
('Imagine a better solution and make it look good', 'Interest'),
('Analyze trends and choose the smartest direction', 'Interest'),
('Building apps, platforms, or systems', 'Interest'),
('Planning events, campaigns, or community programs', 'Interest'),
('Designing visuals, layouts, or brand materials', 'Interest'),
('Reviewing data and recommending solutions', 'Interest'),
('creative', 'Skill'), ('people', 'Skill'), ('problem-solving', 'Skill'), ('tech', 'Skill'),
('analytical', 'Skill'), ('leadership', 'Skill'), ('strategic', 'Skill'), ('detail', 'Skill'),
('communication', 'Skill'), ('visual', 'Skill'), ('problem', 'Skill');

INSERT INTO CAREER_REQUIREMENT (Career_ID, Item_ID) VALUES
(1,17),(1,18),(1,19),(1,20),
(2,20),(2,21),(2,19),(2,22),
(3,21),(3,19),(3,24),(3,20),
(4,22),(4,18),(4,23),(4,19),
(5,20),(5,21),(5,24),(5,19),
(6,17),(6,18),(6,23),(6,25);
