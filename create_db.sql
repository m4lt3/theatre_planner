--Creating core tables
CREATE TABLE USERS(
  UserID INT AUTO_INCREMENT NOT NULL,
  Name VARCHAR (32) NOT NULL,
  Mail VARCHAR (64) NOT NULL,
  Password CHAR (32) NOT NULL,
  Admin BOOLEAN DEFAULT 0,
  PRIMARY KEY (UserID)
);

CREATE TABLE ROLES(
  RoleID INT AUTO_INCREMENT NOT NULL,
  Name VARCHAR(32) NOT NULL,
  Description VARCHAR(512),
  PRIMARY KEY (RoleID)
);

CREATE TABLE SCENES(
  SceneID INT AUTO_INCREMENT NOT NULL,
  Name VARCHAR(32) NOT NULL,
  Description VARCHAR(512),
  PRIMARY KEY (SceneID)
);

CREATE TABLE PRACTICES(
  PracticeID INT AUTO_INCREMENT NOT NULL,
  start DATETIME,
  PRIMARY KEY (PracticeID)
);

--Creating relations
CREATE TABLE PLAYS(
  PlaysID INT AUTO_INCREMENT NOT NULL,
  UserID INT NOT NULL,
  RoleID INT NOT NULL,
  PRIMARY KEY (PlaysID),
  FOREIGN KEY (UserID) REFERENCES USERS(UserID),
  FOREIGN KEY (RoleID) REFERENCES ROLES(RoleID)
);

CREATE TABLE FEATURES(
  FeatureID INT AUTO_INCREMENT NOT NULL,
  SceneID INT NOT NULL,
  RoleID INT NOT NULL,
  Mandatory BOOLEAN DEFAULT 1,
  PRIMARY KEY (FeatureID),
  FOREIGN KEY (SceneID) REFERENCES SCENES(SceneID),
  FOREIGN KEY (RoleID) REFERENCES ROLES(RoleID)
);

CREATE TABLE ATTENDS(
  AttendsID INT AUTO_INCREMENT NOT NULL,
  PracticeID INT NOT NULL,
  UserID INT NOT NULL,
  PRIMARY KEY (AttendsID),
  FOREIGN KEY (PracticeID) REFERENCES PRACTICES(PracticeID),
  FOREIGN KEY (UserID) REFERENCES USERS(UserID)
);
