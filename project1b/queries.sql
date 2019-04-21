# The names of all the actors in the movie 'Die Another Day'
SELECT CONCAT(A.first, ' ', A.last) 
FROM Actor A, Movie M, MovieActor MA
WHERE M.title = 'Die Another Day' AND M.id = MA.mid AND MA.aid = A.id;

# The count of all the actors who acted in multiple movies
SELECT COUNT(*) FROM 
(SELECT DISTINCT aid FROM MovieActor GROUP BY aid Having COUNT(*) > 1) A;

# The name of a director who directed maximum number of movies 
SELECT CONCAT(first, ' ', last) FROM Director D, 
(SELECT DISTINCT MD.did, COUNT(*) movie_num 
	FROM MovieDirector MD GROUP BY MD.did) A
 WHERE A.did = D.id AND A.movie_num >= ALL (SELECT COUNT(*) 
	FROM MovieDirector MD1 GROUP BY MD1.did);