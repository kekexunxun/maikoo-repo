package com.maikoo.businessdirectory.service;

import com.maikoo.businessdirectory.model.dto.AdminGroupUserDTO;
import com.maikoo.businessdirectory.model.dto.FileDTO;
import com.maikoo.businessdirectory.model.dto.GroupUserDTO;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;

import java.util.List;

public interface GroupUserService {
    void save(GroupUserQuery groupUserQuery);

    void remove(GroupUserQuery groupUserQuery);

    void insertFavor(GroupUserQuery groupUserQuery);

    void removeFavor(GroupUserQuery groupUserQuery);

    void updateReview(GroupUserQuery groupUserQuery);

    /**
     * 用户申请的群用户详情
     * 审核列表里面的用户详情
     * @param groupUserQuery
     * @return 群用户详情
     */
    GroupUserDTO userInformation(GroupUserQuery groupUserQuery);

    /**
     * 管理员审核的群用户详情
     *
     * @param groupUserQuery
     * @return
     */
    GroupUserDTO adminInformation(GroupUserQuery groupUserQuery);

    /**
     * 群用户详情
     * 一般的群的用户详情
     * @param groupUserQuery
     * @return
     */
    GroupUserDTO information(GroupUserQuery groupUserQuery);

    /**
     * 用户申请列表
     *
     * @param pageNumber
     * @return
     */
    List<GroupUserDTO> userApplyList(int pageNumber);

    /**
     * 群管理员审核列表
     *
     * @param pageNumber
     * @return
     */
    List<GroupUserDTO> adminApplyList(int pageNumber);

    List<GroupUserDTO> favorList(int pageNumber);

    List<GroupUserDTO> selectByGroup(GroupUserQuery groupUserQuery);

    /**
     * 后台通过groupId相应群的成员列表
     * @param groupId
     * @return
     */
    List<AdminGroupUserDTO> getUserListByGroupId(long groupId);

    /**
     *
     * @return
     */
    FileDTO userExcel(long groupId);


    GroupUserDTO userInfoByAdmin(GroupUserQuery groupUserQuery);

    /**
     * 搜索用户
     * @param groupUserQuery
     * @return
     */
    List<GroupUserDTO> searchUserInfo(GroupUserQuery groupUserQuery);

    /**
     * 是否为指定群的成员
     * @param groupUserQuery
     * @return
     */
    GroupUserDTO isMember(GroupUserQuery groupUserQuery);

    GroupUserDTO isApply(GroupUserQuery groupUserQuery);
}
