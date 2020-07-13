package com.maikoo.businessdirectory.controller.front;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.factory.GroupUserFactory;
import com.maikoo.businessdirectory.model.dto.GroupUserDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import com.maikoo.businessdirectory.model.view.GroupUserView;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestAttribute;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

import javax.servlet.http.HttpSession;
import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api/group/user")
public class GroupUserController {
    @Autowired
    private GroupUserFactory groupUserFactory;
    @Autowired
    private HttpSession session;

    @JsonView(GroupUserView.List.class)
    @RequestMapping("/list")
    public ResponseDTO<List<GroupUserDTO>> classList(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).selectByGroup(groupUserQuery));
    }

    @JsonView(GroupUserView.BaseDetailList.class)
    @RequestMapping("/class/detail-list")
    public ResponseDTO<List<GroupUserDTO>> classDetailList(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).selectByGroup(groupUserQuery));
    }

    @JsonView(GroupUserView.BaseDetailList.class)
    @RequestMapping("/school/detail-list")
    public ResponseDTO<List<GroupUserDTO>> schoolDetailList(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).selectByGroup(groupUserQuery));
    }

    @JsonView(GroupUserView.CommunityDetailList.class)
    @RequestMapping("/community/detail-list")
    public ResponseDTO<List<GroupUserDTO>> communityDetailList(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).selectByGroup(groupUserQuery));
    }

    @JsonView(GroupUserView.BaseDetailList.class)
    @RequestMapping("/country/detail-list")
    public ResponseDTO<List<GroupUserDTO>> countryDetailList(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).selectByGroup(groupUserQuery));
    }

    @JsonView(GroupUserView.ClassInformation.class)
    @RequestMapping("/class/personal-information")
    public ResponseDTO<GroupUserDTO> classPersonalInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).information(groupUserQuery));
    }

    @JsonView(GroupUserView.SchoolInformation.class)
    @RequestMapping("/school/personal-information")
    public ResponseDTO<GroupUserDTO> schoolPersonalInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).information(groupUserQuery));
    }

    @JsonView(GroupUserView.CommunityInformation.class)
    @RequestMapping("/community/personal-information")
    public ResponseDTO<GroupUserDTO> communityPersonalInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).information(groupUserQuery));
    }

    @JsonView(GroupUserView.CountryInformation.class)
    @RequestMapping("/country/personal-information")
    public ResponseDTO<GroupUserDTO> countryPersonalInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).information(groupUserQuery));
    }

    @JsonView(GroupUserView.ClassInformationAndFavor.class)
    @RequestMapping("/class/others-information")
    public ResponseDTO<GroupUserDTO> classOthersInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).information(groupUserQuery));
    }

    @JsonView(GroupUserView.SchoolInformationAndFavor.class)
    @RequestMapping("/school/others-information")
    public ResponseDTO<GroupUserDTO> schoolOthersInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).information(groupUserQuery));
    }

    @JsonView(GroupUserView.CommunityInformationAndFavor.class)
    @RequestMapping("/community/others-information")
    public ResponseDTO<GroupUserDTO> communityOthersInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).information(groupUserQuery));
    }

    @JsonView(GroupUserView.CountryInformationAndFavor.class)
    @RequestMapping("/country/others-information")
    public ResponseDTO<GroupUserDTO> countryOthersInformation(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).information(groupUserQuery));
    }

    @RequestMapping("/save")
    public ResponseDTO save(GroupUserQuery groupUserQuery) {
        groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).save(groupUserQuery);
        return new ResponseDTO(200, "修改成功");
    }

    @RequestMapping("/remove")
    public ResponseDTO remove(GroupUserQuery groupUserQuery) {
        groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).remove(groupUserQuery);
        return new ResponseDTO(200, "删除成功");
    }

    /**
     * 查询自己的申请列表
     *
     * @param groupUserQuery
     * @return
     */
    @JsonView(GroupUserView.ApplyList.class)
    @RequestMapping("/apply")
    public ResponseDTO<List<GroupUserDTO>> applyList(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).userApplyList(groupUserQuery.getPageNum()));
    }

    /**
     * 管理员审核列表
     *
     * @param groupUserQuery
     * @return
     */
    @JsonView(GroupUserView.ReviewList.class)
    @RequestMapping("/review")
    public ResponseDTO<List<GroupUserDTO>> reviewList(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).adminApplyList(groupUserQuery.getPageNum()));
    }

    @RequestMapping("/review/update")
    public ResponseDTO updateReview(GroupUserQuery groupUserQuery) {
        groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).updateReview(groupUserQuery);
        return new ResponseDTO(200, "审核成功");
    }

    @JsonView(GroupUserView.FavorList.class)
    @RequestMapping("/favor")
    public ResponseDTO<List<GroupUserDTO>> favorList(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).favorList(groupUserQuery.getPageNum()));
    }

    @RequestMapping("/favor/insert")
    public ResponseDTO insertFavor(GroupUserQuery groupUserQuery) {
        groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).insertFavor(groupUserQuery);
        return new ResponseDTO(200, "收藏成功");
    }

    @RequestMapping("/favor/delete")
    public ResponseDTO deleteFavor(GroupUserQuery groupUserQuery) {
        groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).removeFavor(groupUserQuery);
        return new ResponseDTO(200, "取消收藏成功");
    }

    @JsonView(GroupUserView.SchoolApplyInformation.class)
    @RequestMapping("/school/apply/information")
    public ResponseDTO<GroupUserDTO> schoolApplyInformation(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).userInformation(groupUserQuery));
    }

    @JsonView(GroupUserView.ClassApplyInformation.class)
    @RequestMapping("/class/apply/information")
    public ResponseDTO<GroupUserDTO> classApplyInformation(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).userInformation(groupUserQuery));
    }

    @JsonView(GroupUserView.CommunityApplyInformation.class)
    @RequestMapping("/community/apply/information")
    public ResponseDTO<GroupUserDTO> communityApplyInformation(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).userInformation(groupUserQuery));
    }

    @JsonView(GroupUserView.CountryApplyInformation.class)
    @RequestMapping("/country/apply/information")
    public ResponseDTO<GroupUserDTO> countryApplyInformation(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).userInformation(groupUserQuery));
    }

    @JsonView(GroupUserView.SchoolReviewInformation.class)
    @RequestMapping("/school/review/information")
    public ResponseDTO<GroupUserDTO> schoolReviewInformation(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).adminInformation(groupUserQuery));
    }

    @JsonView(GroupUserView.ClassReviewInformation.class)
    @RequestMapping("/class/review/information")
    public ResponseDTO<GroupUserDTO> classReviewInformation(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).adminInformation(groupUserQuery));
    }

    @JsonView(GroupUserView.CommunityReviewInformation.class)
    @RequestMapping("/community/review/information")
    public ResponseDTO<GroupUserDTO> communityReviewInformation(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).adminInformation(groupUserQuery));
    }

    @JsonView(GroupUserView.CountryReviewInformation.class)
    @RequestMapping("/country/review/information")
    public ResponseDTO<GroupUserDTO> countryReviewInformation(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).adminInformation(groupUserQuery));
    }


    @JsonView(GroupUserView.BaseDetailList.class)
    @RequestMapping("/class/search")
    public ResponseDTO<List<GroupUserDTO>> searchClassUserInfo(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).searchUserInfo(groupUserQuery));
    }

    @JsonView(GroupUserView.CommunityDetailList.class)
    @RequestMapping("/community/search")
    public ResponseDTO<List<GroupUserDTO>> searchCommunityUserInfo(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).searchUserInfo(groupUserQuery));
    }

    @JsonView(GroupUserView.BaseDetailList.class)
    @RequestMapping("/country/search")
    public ResponseDTO<List<GroupUserDTO>> searchCountryUserInfo(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).searchUserInfo(groupUserQuery));
    }

    @JsonView(GroupUserView.BaseDetailList.class)
    @RequestMapping("/school/search")
    public ResponseDTO<List<GroupUserDTO>> searchSchoolUserInfo(GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).searchUserInfo(groupUserQuery));
    }


    @JsonView(GroupUserView.IsMember.class)
    @ResponseBody
    @RequestMapping("/class/member")
    public ResponseDTO<GroupUserDTO> isClassMember(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).isMember(groupUserQuery));
    }

    @JsonView(GroupUserView.IsMember.class)
    @RequestMapping("/school/member")
    @ResponseBody
    public ResponseDTO<GroupUserDTO> isSchoolMember(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).isMember(groupUserQuery));
    }

    @JsonView(GroupUserView.IsMember.class)
    @RequestMapping("/country/member")
    @ResponseBody
    public ResponseDTO<GroupUserDTO> isCountryMember(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).isMember(groupUserQuery));
    }

    @JsonView(GroupUserView.IsMember.class)
    @RequestMapping("/community/member")
    @ResponseBody
    public ResponseDTO<GroupUserDTO> isCommunityMember(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200, "获取成功", groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).isMember(groupUserQuery));
    }

    @JsonView(GroupUserView.UserApply.class)
    @RequestMapping("/apply/class/confirm")
    public ResponseDTO<GroupUserDTO> isClassApply(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200,"获取成功",groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).isApply(groupUserQuery));
    }

    @JsonView(GroupUserView.UserApply.class)
    @RequestMapping("/apply/country/confirm")
    public ResponseDTO<GroupUserDTO> isCountryApply(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200,"获取成功",groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).isApply(groupUserQuery));
    }

    @JsonView(GroupUserView.UserApply.class)
    @RequestMapping("/apply/community/confirm")
    public ResponseDTO<GroupUserDTO> isCommunityApply(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200,"获取成功",groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).isApply(groupUserQuery));
    }

    @JsonView(GroupUserView.UserApply.class)
    @RequestMapping("/apply/school/confirm")
    public ResponseDTO<GroupUserDTO> isSchoolApply(@RequestAttribute("groupUserQuery") GroupUserQuery groupUserQuery) {
        return new ResponseDTO<>(200,"获取成功",groupUserFactory.getGroupUserService(groupUserQuery.getGroupType()).isApply(groupUserQuery));
    }


}
