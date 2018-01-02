DROP TRIGGER IF EXISTS ADD_COMMENT;
CREATE TRIGGER ADD_COMMENT AFTER INSERT ON comments
FOR EACH ROW
BEGIN
    UPDATE articles SET comments_count = comments_count + 1 WHERE id = NEW.article_id;
END;

DROP TRIGGER IF EXISTS ADD_COMMENT_REPLY;
CREATE TRIGGER ADD_COMMENT_REPLY AFTER INSERT ON comment_replies
FOR EACH ROW
BEGIN
    UPDATE articles JOIN comments c ON c.id = NEW.comment_id SET comments_count = comments_count + 1 WHERE articles.id = c.article_id;
END;

DROP TRIGGER IF EXISTS REMOVE_COMMENT;
CREATE TRIGGER REMOVE_COMMENT AFTER DELETE ON comments
FOR EACH ROW
BEGIN
    UPDATE articles SET comments_count = comments_count - 1 WHERE id = OLD.article_id;
END;

DROP TRIGGER IF EXISTS REMOVE_COMMENT_REPLY;
CREATE TRIGGER REMOVE_COMMENT_REPLY AFTER DELETE ON comment_replies
FOR EACH ROW
BEGIN
    UPDATE articles JOIN comments c ON c.id = OLD.comment_id SET comments_count = comments_count - 1 WHERE articles.id = c.article_id;
END;