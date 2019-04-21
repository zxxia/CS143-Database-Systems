
# Each movie must have one unique id and id cannot be null.
# Each movie must have a title.
# Check if id is a non-negative integer.
# Check if movie production year is later than 1700. I assume all movies 
# were produced after 1700.
CREATE TABLE Movie(id INT NOT NULL, title VARCHAR(100) NOT NULL, year INT, 
	rating VARCHAR(10), company VARCHAR(50), PRIMARY KEY(id), CHECK(id >= 0)) ENGINE = INNODB;

#CHECK(year >= 1700)

# Each actor must have one unique id and id cannot be null.
# Each actor must have a date of birth.
# Check if id is a non-negative integer.
CREATE TABLE Actor(id INT NOT NULL, last VARCHAR(20), first VARCHAR(20),
	sex VARCHAR(6), dob DATE NOT NULL, dod DATE, PRIMARY KEY(id), CHECK(id >= 0))
ENGINE = INNODB;


# Each director must have one unique id and id cannot be null.
# Each director must have a date of birth.
# Check if id is a non-negative integer.
CREATE TABLE Director(id INT NOT NULL, last VARCHAR(20), first VARCHAR(20),
	dob DATE NOT NULL, dod DATE, PRIMARY KEY(id), CHECK(id >= 0)) ENGINE = INNODB;


# Each tuple must have an mid which also appears in id attribute of Movie.
CREATE TABLE MovieGenre(mid INT NOT NULL, genre VARCHAR(20),
	PRIMARY KEY (mid),
	FOREIGN KEY (mid) REFERENCES Movie(id) ON DELETE CASCADE ON UPDATE CASCADE)
ENGINE = INNODB;


# Each tuple must have an mid which also appears in id attribute of Movie.
# Each tuple must have a did which also appear in id attribute of Director.
CREATE TABLE MovieDirector(mid INT, did INT,
	PRIMARY KEY (mid, did),
	FOREIGN KEY (mid) REFERENCES Movie(id) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (did) REFERENCES Director(id) ON DELETE CASCADE ON UPDATE CASCADE)
ENGINE = INNODB;


# Each tuple must have an mid which also appears in id attribute of Movie.
# Each tuple must have a did which also appear in id attribute of Actor.
CREATE TABLE MovieActor(mid INT, aid INT, role VARCHAR(50),
	PRIMARY KEY(mid, aid),
	FOREIGN KEY (mid) REFERENCES Movie(id) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (aid) REFERENCES Actor(id) ON DELETE CASCADE ON UPDATE CASCADE)
ENGINE = INNODB;


# Each tuple must have an mid which also appears in id attribute of Movie.
# Check if rating is between 0 and 5 inclusively.
CREATE TABLE Review(name VARCHAR(20), time TIMESTAMP, mid INT, rating INT, 
	comment VARCHAR(500), 
	FOREIGN KEY (mid) REFERENCES Movie(id) ON DELETE CASCADE ON UPDATE CASCADE,
	CHECK(rating >= 0 AND rating <=5)) ENGINE = INNODB;


# The tuple must have a did which also appear in id attribute of Actor.
CREATE TABLE MaxPersonID(id INT);
#FOREIGN KEY (id) REFERENCES Actor(id) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE = INNODB;

# The tuple must have an mid which also appears in id attribute of Movie.
CREATE TABLE MaxMovieID(id INT);
#FOREIGN KEY (id) REFERENCES Movie(id) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE = INNODB;