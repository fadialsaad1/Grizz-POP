
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    role ENUM('Organizer','Attendee','Crew') NOT NULL
);

CREATE TABLE Events (
    EventID INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(100),
    date DATE,
    time TIME,
    status ENUM('Planned','Scheduled','Completed','Cancelled'),
    organizerID INT,
    FOREIGN KEY (organizerID) REFERENCES Users(UserID)
);

CREATE TABLE Feedback (
    FeedbackID INT AUTO_INCREMENT PRIMARY KEY,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    EventID INT,
    UserID INT,
    FOREIGN KEY (EventID) REFERENCES Events(EventID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

CREATE TABLE RSVP (
    rsvpID INT AUTO_INCREMENT PRIMARY KEY,
    response ENUM('Yes','No','Maybe'),
    EventID INT,
    UserID INT,
    FOREIGN KEY (EventID) REFERENCES Events(EventID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

CREATE TABLE CrewAssignment (
    AssignmentID INT AUTO_INCREMENT PRIMARY KEY,
    setupStatus ENUM('Pending','In Progress','Completed'),
    issueReport TEXT,
    EventID INT,
    crewID INT,
    FOREIGN KEY (EventID) REFERENCES Events(EventID),
    FOREIGN KEY (crewID) REFERENCES Users(UserID)
);











INSERT INTO Users (firstname, lastname, email, phone, role) VALUES
('Alice', 'Johnson', 'alice.johnson@example.com', '555-1111', 'Organizer'),
('Bob', 'Smith', 'bob.smith@example.com', '555-2222', 'Attendee'),
('Charlie', 'Lee', 'charlie.lee@example.com', '555-3333', 'Crew'),
('David', 'Wong', 'david.wong@example.com', '555-4444', 'Attendee'),
('Eva', 'Martinez', 'eva.martinez@example.com', '555-5555', 'Crew');

INSERT INTO Events (title, description, location, date, time, status, organizerID) VALUES
('Charity Run', 'Community 5K fundraising event', 'City Park', '2025-05-12', '09:00:00', 'Scheduled', 1),
('Music Festival', 'Live music and food trucks', 'Downtown Plaza', '2025-06-20', '14:00:00', 'Planned', 1),
('Tech Expo', 'Technology innovation showcase', 'Convention Center', '2025-07-15', '10:00:00', 'Planned', 1),
('Cleanup Drive', 'Neighborhood cleanup event', 'Greenwood Park', '2025-04-10', '08:00:00', 'Completed', 1),
('Art Exhibit', 'Local artists display their work', 'Art Hall', '2025-08-22', '11:00:00', 'Planned', 1);

INSERT INTO RSVP (response, EventID, UserID) VALUES
('Yes', 1, 2),
('Maybe', 1, 4),
('Yes', 2, 2),
('No', 2, 4),
('Yes', 3, 2);

INSERT INTO CrewAssignment (setupStatus, issueReport, EventID, crewID) VALUES
('In Progress', 'Sound system delay', 2, 3),
('Completed', 'All set', 1, 5),
('Pending', 'Need to arrange tables', 3, 3),
('Completed', 'Venue cleaned up', 4, 5),
('Pending', 'Awaiting materials', 5, 3);

INSERT INTO Feedback (rating, comment, EventID, UserID) VALUES
(5, 'Great organization and turnout!', 1, 2),
(4, 'Good event but parking was limited.', 2, 4),
(5, 'Loved the atmosphere.', 3, 2),
(3, 'Could use more volunteers.', 4, 2),
(4, 'Excellent setup and coordination.', 1, 4);
