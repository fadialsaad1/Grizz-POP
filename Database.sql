
CREATE TABLE users (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    role ENUM('Organizer','Attendee','Crew') NOT NULL
);

CREATE TABLE events (
    eventID INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(100),
    date DATE,
    time TIME,
    status ENUM('Planned','Scheduled','Completed','Cancelled'),
    organizerID INT,
    FOREIGN KEY (organizerID) REFERENCES Users(UserID)
);

CREATE TABLE feedback (
    feedbackID INT AUTO_INCREMENT PRIMARY KEY,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    eventID INT,
    userID INT,
    FOREIGN KEY (EventID) REFERENCES Events(EventID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

CREATE TABLE RSVP (
    rsvpID INT AUTO_INCREMENT PRIMARY KEY,
    response ENUM('Yes','No','Maybe'),
    eventID INT,
    userID INT,
    FOREIGN KEY (EventID) REFERENCES Events(EventID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

CREATE TABLE CrewAssignment (
    assignmentID INT AUTO_INCREMENT PRIMARY KEY,
    setupStatus ENUM('Pending','In Progress','Completed'),
    issueReport TEXT,
    eventID INT,
    crewID INT,
    FOREIGN KEY (EventID) REFERENCES Events(EventID),
    FOREIGN KEY (crewID) REFERENCES Users(UserID)
);



INSERT INTO Users (firstname, lastname, email, phone, role) VALUES
('Alice', 'Johnson', 'alice.johnson@gmail.com', '312-485-9271', 'Organizer'),
('Bob', 'Smith', 'bob.smith@gmail.com', '714-203-6598', 'Attendee'),
('Charlie', 'Lee', 'charlie.lee@gmail.com', '432-644-8914', 'Crew'),
('David', 'Wong', 'david.wong@gmail.com', '248-532-8253', 'Attendee'),
('Eva', 'Martinez', 'eva.martinez@gmail.com', '307-500-6864', 'Crew');

INSERT INTO events (title, description, location, date, time, status, organizerID) VALUES
('Charity Run', 'Community 5K fundraising event', 'City Park', '2025-05-12', '09:00:00', 'Scheduled', 1),
('Music Festival', 'Live music and food trucks', 'Downtown Plaza', '2025-06-20', '14:00:00', 'Planned', 1),
('Tech Expo', 'Technology innovation showcase', 'Convention Center', '2025-07-15', '10:00:00', 'Planned', 1),
('Cleanup Drive', 'Neighborhood cleanup event', 'Greenwood Park', '2025-04-10', '08:00:00', 'Completed', 1),
('Art Exhibit', 'Local artists display their work', 'Art Hall', '2025-08-22', '11:00:00', 'Planned', 1);

INSERT INTO rSVP (response, eventID, userID) VALUES
('Yes', 1, 2),
('Maybe', 1, 4),
('Yes', 2, 2),
('No', 2, 4),
('Yes', 3, 2);

INSERT INTO crewAssignment (setupStatus, issueReport, eventID, crewID) VALUES
('In Progress', 'Sound system delay', 2, 3),
('Completed', 'All set', 1, 5),
('Pending', 'Need to arrange tables', 3, 3),
('Completed', 'Venue cleaned up', 4, 5),
('Pending', 'Awaiting materials', 5, 3);

INSERT INTO feedback (rating, comment, eventID, userID) VALUES
(5, 'Great organization and turnout!', 1, 2),
(4, 'Good event but parking was limited.', 2, 4),
(5, 'Loved the atmosphere.', 3, 2),
(3, 'Could use more volunteers.', 4, 2),
(4, 'Excellent setup and coordination.', 1, 4);
