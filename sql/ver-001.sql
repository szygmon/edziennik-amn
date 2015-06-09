CREATE SEQUENCE roles_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE TABLE roles (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE TABLE users (id INT NOT NULL, email VARCHAR(255) NOT NULL, username TEXT NOT NULL, password VARCHAR(255) NOT NULL, givenName VARCHAR(255) DEFAULT NULL, familyName VARCHAR(255) DEFAULT NULL, isActive BOOLEAN NOT NULL, ts TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, lastLogin TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, dtype VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email);
CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username);
CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, PRIMARY KEY(user_id, role_id));
CREATE INDEX IDX_2DE8C6A3A76ED395 ON user_role (user_id);
CREATE INDEX IDX_2DE8C6A3D60322AC ON user_role (role_id);
CREATE TABLE Student (id INT NOT NULL, registrationNr VARCHAR(45) DEFAULT NULL, pesel VARCHAR(11) DEFAULT NULL, birthdate DATE DEFAULT NULL, birthplace VARCHAR(45) DEFAULT NULL, PRIMARY KEY(id));
ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Student ADD CONSTRAINT FK_789E96AFBF396750 FOREIGN KEY (id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

CREATE SEQUENCE Clas_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE Classroom_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE groups_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE Hour_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE Plan_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE Semester_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE Subject_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE Year_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE TABLE Clas (id INT NOT NULL, year_id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_5CC260EB40C1FEA7 ON Clas (year_id);
CREATE TABLE Classroom (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE TABLE groups (id INT NOT NULL, name VARCHAR(255) NOT NULL, mainGroup_id INT DEFAULT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_F06D39708721F385 ON groups (mainGroup_id);
CREATE TABLE Hour (id INT NOT NULL, fromTime TIME(0) WITHOUT TIME ZONE NOT NULL, toTime TIME(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id));
CREATE TABLE Plan (id INT NOT NULL, subject_id INT NOT NULL, teacher_id INT NOT NULL, classroom_id INT DEFAULT NULL, class_id INT NOT NULL, group_id INT DEFAULT NULL, hour INT NOT NULL, day INT NOT NULL, fromDate DATE NOT NULL, toDate DATE NOT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_7D68F44323EDC87 ON Plan (subject_id);
CREATE INDEX IDX_7D68F44341807E1D ON Plan (teacher_id);
CREATE INDEX IDX_7D68F4436278D5A8 ON Plan (classroom_id);
CREATE INDEX IDX_7D68F443EA000B10 ON Plan (class_id);
CREATE INDEX IDX_7D68F443FE54D947 ON Plan (group_id);
CREATE TABLE Semester (id INT NOT NULL, year_id INT NOT NULL, semester INT NOT NULL, fromDate DATE NOT NULL, toDate DATE NOT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_E4EECBB40C1FEA7 ON Semester (year_id);
CREATE TABLE students_class (student_id INT NOT NULL, clas_id INT NOT NULL, PRIMARY KEY(student_id, clas_id));
CREATE INDEX IDX_E1587E6BCB944F1A ON students_class (student_id);
CREATE INDEX IDX_E1587E6B1CDC0B8A ON students_class (clas_id);
CREATE TABLE Subject (id INT NOT NULL, subject VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE TABLE Teacher (id INT NOT NULL, stopien INT DEFAULT NULL, PRIMARY KEY(id));
CREATE TABLE Year (id INT NOT NULL, fromYear INT NOT NULL, toYear INT NOT NULL, PRIMARY KEY(id));
ALTER TABLE Clas ADD CONSTRAINT FK_5CC260EB40C1FEA7 FOREIGN KEY (year_id) REFERENCES Year (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE groups ADD CONSTRAINT FK_F06D39708721F385 FOREIGN KEY (mainGroup_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Plan ADD CONSTRAINT FK_7D68F44323EDC87 FOREIGN KEY (subject_id) REFERENCES Subject (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Plan ADD CONSTRAINT FK_7D68F44341807E1D FOREIGN KEY (teacher_id) REFERENCES Teacher (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Plan ADD CONSTRAINT FK_7D68F4436278D5A8 FOREIGN KEY (classroom_id) REFERENCES Classroom (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Plan ADD CONSTRAINT FK_7D68F443EA000B10 FOREIGN KEY (class_id) REFERENCES Clas (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Plan ADD CONSTRAINT FK_7D68F443FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Semester ADD CONSTRAINT FK_E4EECBB40C1FEA7 FOREIGN KEY (year_id) REFERENCES Year (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE students_class ADD CONSTRAINT FK_E1587E6BCB944F1A FOREIGN KEY (student_id) REFERENCES Student (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE students_class ADD CONSTRAINT FK_E1587E6B1CDC0B8A FOREIGN KEY (clas_id) REFERENCES Clas (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Teacher ADD CONSTRAINT FK_7F4B9F49BF396750 FOREIGN KEY (id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

CREATE SEQUENCE Attendance_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE Lesson_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE Notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE Rating_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE RatingDesc_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE TABLE Attendance (id INT NOT NULL, student_id INT NOT NULL, lesson_id INT NOT NULL, presence INT NOT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_22BE0E41CB944F1A ON Attendance (student_id);
CREATE INDEX IDX_22BE0E41CDF80196 ON Attendance (lesson_id);
CREATE TABLE Lesson (id INT NOT NULL, teacher_id INT NOT NULL, class_id INT NOT NULL, subject_id INT NOT NULL, hour_id INT NOT NULL, group_id INT DEFAULT NULL, date DATE NOT NULL, topic VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_FFD871C541807E1D ON Lesson (teacher_id);
CREATE INDEX IDX_FFD871C5EA000B10 ON Lesson (class_id);
CREATE INDEX IDX_FFD871C523EDC87 ON Lesson (subject_id);
CREATE INDEX IDX_FFD871C5B5937BF9 ON Lesson (hour_id);
CREATE INDEX IDX_FFD871C5FE54D947 ON Lesson (group_id);
CREATE TABLE Notification (id INT NOT NULL, user_id INT NOT NULL, msg VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_A765AD32A76ED395 ON Notification (user_id);
CREATE TABLE Rating (id INT NOT NULL, student_id INT NOT NULL, subject_id INT NOT NULL, class_id INT NOT NULL, value VARCHAR(20) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, ratingDesc_id INT NOT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_DF252314CB944F1A ON Rating (student_id);
CREATE INDEX IDX_DF25231423EDC87 ON Rating (subject_id);
CREATE INDEX IDX_DF252314AD314326 ON Rating (ratingDesc_id);
CREATE INDEX IDX_DF252314EA000B10 ON Rating (class_id);
CREATE TABLE RatingDesc (id INT NOT NULL, class_id INT NOT NULL, semester_id INT NOT NULL, subject_id INT NOT NULL, orderDesc INT NOT NULL, weight INT NOT NULL, description VARCHAR(255) NOT NULL, shortDesc VARCHAR(5) NOT NULL, color VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_B06824B9EA000B10 ON RatingDesc (class_id);
CREATE INDEX IDX_B06824B94A798B6F ON RatingDesc (semester_id);
CREATE INDEX IDX_B06824B923EDC87 ON RatingDesc (subject_id);
CREATE TABLE students_groups (student_id INT NOT NULL, group_id INT NOT NULL, PRIMARY KEY(student_id, group_id));
CREATE INDEX IDX_4AB11C12CB944F1A ON students_groups (student_id);
CREATE INDEX IDX_4AB11C12FE54D947 ON students_groups (group_id);
ALTER TABLE Attendance ADD CONSTRAINT FK_22BE0E41CB944F1A FOREIGN KEY (student_id) REFERENCES Student (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Attendance ADD CONSTRAINT FK_22BE0E41CDF80196 FOREIGN KEY (lesson_id) REFERENCES Lesson (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Lesson ADD CONSTRAINT FK_FFD871C541807E1D FOREIGN KEY (teacher_id) REFERENCES Teacher (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Lesson ADD CONSTRAINT FK_FFD871C5EA000B10 FOREIGN KEY (class_id) REFERENCES Clas (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Lesson ADD CONSTRAINT FK_FFD871C523EDC87 FOREIGN KEY (subject_id) REFERENCES Subject (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Lesson ADD CONSTRAINT FK_FFD871C5B5937BF9 FOREIGN KEY (hour_id) REFERENCES Hour (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Lesson ADD CONSTRAINT FK_FFD871C5FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Notification ADD CONSTRAINT FK_A765AD32A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Rating ADD CONSTRAINT FK_DF252314CB944F1A FOREIGN KEY (student_id) REFERENCES Student (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Rating ADD CONSTRAINT FK_DF25231423EDC87 FOREIGN KEY (subject_id) REFERENCES Subject (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Rating ADD CONSTRAINT FK_DF252314AD314326 FOREIGN KEY (ratingDesc_id) REFERENCES RatingDesc (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE Rating ADD CONSTRAINT FK_DF252314EA000B10 FOREIGN KEY (class_id) REFERENCES Clas (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE RatingDesc ADD CONSTRAINT FK_B06824B9EA000B10 FOREIGN KEY (class_id) REFERENCES Clas (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE RatingDesc ADD CONSTRAINT FK_B06824B94A798B6F FOREIGN KEY (semester_id) REFERENCES Semester (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE RatingDesc ADD CONSTRAINT FK_B06824B923EDC87 FOREIGN KEY (subject_id) REFERENCES Subject (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE students_groups ADD CONSTRAINT FK_4AB11C12CB944F1A FOREIGN KEY (student_id) REFERENCES Student (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE students_groups ADD CONSTRAINT FK_4AB11C12FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE classroom ADD seats INT DEFAULT NULL;
ALTER TABLE classroom ADD projector BOOLEAN NOT NULL;
ALTER TABLE classroom ADD others VARCHAR(255) DEFAULT NULL;
ALTER TABLE plan RENAME COLUMN hour TO hour_id;
ALTER TABLE plan ADD CONSTRAINT FK_7D68F443B5937BF9 FOREIGN KEY (hour_id) REFERENCES Hour (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
CREATE INDEX IDX_7D68F443B5937BF9 ON plan (hour_id);

CREATE TABLE educators (clas_id INT NOT NULL, teacher_id INT NOT NULL, PRIMARY KEY(clas_id, teacher_id));
CREATE INDEX IDX_43ED22A51CDC0B8A ON educators (clas_id);
CREATE INDEX IDX_43ED22A541807E1D ON educators (teacher_id);
ALTER TABLE educators ADD CONSTRAINT FK_43ED22A51CDC0B8A FOREIGN KEY (clas_id) REFERENCES Clas (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE educators ADD CONSTRAINT FK_43ED22A541807E1D FOREIGN KEY (teacher_id) REFERENCES Teacher (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

CREATE TABLE Parents (id INT NOT NULL, PRIMARY KEY(id));
ALTER TABLE Parents ADD CONSTRAINT FK_32ED24F6BF396750 FOREIGN KEY (id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE student ADD parents_id INT DEFAULT NULL;
ALTER TABLE student ADD CONSTRAINT FK_789E96AFB706B6D3 FOREIGN KEY (parents_id) REFERENCES Parents (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
CREATE UNIQUE INDEX UNIQ_789E96AFB706B6D3 ON student (parents_id);