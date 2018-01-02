
CREATE OR REPLACE FUNCTION post_add_comment() RETURNS TRIGGER AS $$
BEGIN
  UPDATE articles SET comments_count = comments_count + 1 WHERE id = NEW.article_id;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS ADD_COMMENT ON comments;
CREATE TRIGGER ADD_COMMENT AFTER INSERT ON comments
  FOR EACH ROW EXECUTE PROCEDURE post_add_comment();

CREATE OR REPLACE FUNCTION post_add_comment_reply() RETURNS TRIGGER AS $$
BEGIN
  UPDATE articles SET comments_count = comments_count + 1 FROM comments c WHERE
    c.id = NEW.comment_id AND articles.id = c.article_id;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS ADD_COMMENT_REPLY ON comment_replies;
CREATE TRIGGER ADD_COMMENT_REPLY AFTER INSERT ON comment_replies
  FOR EACH ROW EXECUTE PROCEDURE post_add_comment_reply();

CREATE OR REPLACE FUNCTION post_remove_comment() RETURNS TRIGGER AS $$
BEGIN
  UPDATE articles SET comments_count = comments_count - 1 WHERE id = OLD.article_id;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS REMOVE_COMMENT ON comments;
CREATE TRIGGER REMOVE_COMMENT AFTER DELETE ON comments
  FOR EACH ROW EXECUTE PROCEDURE post_remove_comment();

CREATE OR REPLACE FUNCTION post_remove_comment_reply() RETURNS TRIGGER AS $$
BEGIN
  UPDATE articles SET comments_count = comments_count - 1 FROM comments c WHERE
    c.id = OLD.comment_id AND articles.id = c.article_id;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS REMOVE_COMMENT_REPLY ON comment_replies;
CREATE TRIGGER REMOVE_COMMENT_REPLY AFTER DELETE ON comment_replies
  FOR EACH ROW EXECUTE PROCEDURE post_remove_comment_reply();