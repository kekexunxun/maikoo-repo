package com.maikoo.businessdirectory.model.dto;


import com.fasterxml.jackson.annotation.JsonView;
import com.fasterxml.jackson.databind.annotation.JsonSerialize;
import com.maikoo.businessdirectory.model.UserDO;
import com.maikoo.businessdirectory.model.serializer.BaseDateTimeSerializer;
import com.maikoo.businessdirectory.model.view.UserView;
import lombok.Data;

@Data
public class UserDTO {
    @JsonView(value = {UserView.Base.class})
    private String nickname;
    @JsonView(value = {UserView.Base.class})
    private String avatarUrl;

    @JsonView(value = {UserView.UserList.class})
    private long userId;
    @JsonView(value = {UserView.UserList.class})
    private String userName;
    //用户微信头像
    @JsonView(value = {UserView.UserList.class})
    private String userAvatarUrl;
    @JsonView(value = {UserView.UserList.class})
    private String userMobile;
    @JsonSerialize(using = BaseDateTimeSerializer.class)
    @JsonView(value = {UserView.UserList.class})
    private String createdAt;


    public static UserDTO valueOf(UserDO userDO) {
        UserDTO userDTO = new UserDTO();
        userDTO.setUserId(userDO.getUserId());
        userDTO.setUserName(userDO.getNickname());
        userDTO.setUserAvatarUrl(userDO.getAvatarUrl());
        userDTO.setUserMobile(userDO.getMobile());
        if(userDO.getCreatedAt()!=0){
            userDTO.setCreatedAt(String.valueOf(userDO.getCreatedAt()));
        }
        return userDTO;
    }
}
