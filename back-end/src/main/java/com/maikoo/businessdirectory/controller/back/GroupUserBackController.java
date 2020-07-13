package com.maikoo.businessdirectory.controller.back;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.factory.GroupUserFactory;
import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.dto.AdminGroupUserDTO;
import com.maikoo.businessdirectory.model.dto.FileDTO;
import com.maikoo.businessdirectory.model.dto.GroupUserDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import com.maikoo.businessdirectory.model.view.GroupUserView;
import com.maikoo.businessdirectory.model.view.GroupView;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestAttribute;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

import javax.servlet.http.HttpServletRequest;
import java.util.List;

@Controller
@RequestMapping("/admin/api/user")
public class GroupUserBackController {
    @Autowired
    private GroupUserFactory groupUserFactory;

    @Autowired
    private GroupUserFactory getGroupUserFactory;

    @JsonView(GroupView.Class.class)
    @ResponseBody
    @RequestMapping("/class")
    public ResponseDTO<List<AdminGroupUserDTO>> getClassUserList(Long groupId) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(GroupTypeEnum.CLASS).getUserListByGroupId(groupId));
    }

    @JsonView(GroupView.Country.class)
    @ResponseBody
    @RequestMapping("/country")
    public ResponseDTO<List<AdminGroupUserDTO>> getCountryUserList(Long groupId) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(GroupTypeEnum.COUNTRY).getUserListByGroupId(groupId));
    }

    @JsonView(GroupView.Community.class)
    @ResponseBody
    @RequestMapping("/community")
    public ResponseDTO<List<AdminGroupUserDTO>> getCommunityUserList(Long groupId) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(GroupTypeEnum.COMMUNITY).getUserListByGroupId(groupId));
    }

    @JsonView(GroupView.School.class)
    @ResponseBody
    @RequestMapping("/school")
    public ResponseDTO<List<AdminGroupUserDTO>> getSchoolUserList(Long groupId) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(GroupTypeEnum.SCHOOL).getUserListByGroupId(groupId));
    }

    @RequestMapping("/excel")
    @ResponseBody
    public ResponseDTO<FileDTO> excel(GroupTypeEnum groupType, long groupId) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupType).userExcel(groupId));
    }

    @RequestMapping("/information")
    public String information(GroupUserQuery groupUserQuery, HttpServletRequest request) {
        request.setAttribute("groupUserQuery", groupUserQuery);
        return "forward:/admin/api/user/" + groupUserQuery.getGroupType().toString().toLowerCase() + "/information";
    }

    @JsonView(GroupUserView.ClassUserInformation.class)
    @ResponseBody
    @RequestMapping("/class/information")
    public ResponseDTO<GroupUserDTO> classInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", getGroupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).userInfoByAdmin(groupUserQuery));
    }

    @JsonView(GroupUserView.SchoolUserInformation.class)
    @RequestMapping("/school/information")
    @ResponseBody
    public ResponseDTO<GroupUserDTO> schoolInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", getGroupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).userInfoByAdmin(groupUserQuery));
    }

    @JsonView(GroupUserView.CountryUserInformation.class)
    @RequestMapping("/country/information")
    @ResponseBody
    public ResponseDTO<GroupUserDTO> countryInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", getGroupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).userInfoByAdmin(groupUserQuery));
    }

    @JsonView(GroupUserView.CommunityUserInformation.class)
    @RequestMapping("/community/information")
    @ResponseBody
    public ResponseDTO<GroupUserDTO> communityInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", getGroupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).userInfoByAdmin(groupUserQuery));
    }


}
