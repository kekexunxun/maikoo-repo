package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.FeedbackDO;
import org.apache.ibatis.annotations.Insert;

import java.util.List;

public interface FeedbackDao {

    @Insert("INSERT " +
            "INTO " +
                "comm_feedback(content, mobile, image_url, created_at,user_id) " +
            "VALUES" +
                "(#{content}, #{mobile}, #{imageUrl}, UNIX_TIMESTAMP(NOW(3)),#{userDO.userId})")
    int insert(FeedbackDO feedbackDO);


    List<FeedbackDO> adminGetFeedBackList();

}
