package com.maikoo.businessdirectory.model.query;

import com.maikoo.businessdirectory.model.ClassGroupDO;
import com.maikoo.businessdirectory.model.CommunityGroupDO;
import com.maikoo.businessdirectory.model.CountryGroupDO;
import com.maikoo.businessdirectory.model.SchoolGroupDO;
import lombok.Data;

import java.awt.image.BufferedImage;

@Data
public class PosterQuery {
    private String profilePhoto;
    private String title;
    private String tag;
    private String brief;
    private String address;
    private BufferedImage qrCodeBufferedImage;
    private String posterUrl;

    public static PosterQuery valueOf(SchoolGroupDO schoolGroupDO){
        PosterQuery posterQuery = new PosterQuery();
        posterQuery.setProfilePhoto(schoolGroupDO.getGroupAvatarUrl());
        posterQuery.setTitle(schoolGroupDO.getGroupName()+"同校群");
        posterQuery.setTag(schoolGroupDO.getSchoolName());
        posterQuery.setBrief(schoolGroupDO.getGroupBrief());
        posterQuery.setAddress(schoolGroupDO.getGroupAddrDetail());
        posterQuery.setPosterUrl(schoolGroupDO.getPosterUrl());
        return posterQuery;
    }

    public static PosterQuery valueOf(ClassGroupDO classGroupDO){
        PosterQuery posterQuery = new PosterQuery();
        posterQuery.setProfilePhoto(classGroupDO.getGroupAvatarUrl());
        posterQuery.setTitle(classGroupDO.getGroupName()+"同班群");
        posterQuery.setTag(classGroupDO.getSchoolName()+"_"+classGroupDO.getClassName());
        posterQuery.setBrief(classGroupDO.getGroupBrief());
        posterQuery.setAddress(classGroupDO.getGroupAddrDetail());
        posterQuery.setPosterUrl(classGroupDO.getPosterUrl());
        return posterQuery;
    }

    public static PosterQuery valueOf(CountryGroupDO countryGroupDO){
        PosterQuery posterQuery = new PosterQuery();
        posterQuery.setProfilePhoto(countryGroupDO.getGroupAvatarUrl());
        posterQuery.setTitle(countryGroupDO.getGroupName()+"同乡群");
        posterQuery.setBrief(countryGroupDO.getGroupBrief());
        posterQuery.setAddress(countryGroupDO.getGroupAddrDetail());
        posterQuery.setPosterUrl(countryGroupDO.getPosterUrl());
        return posterQuery;
    }

    public static PosterQuery valueOf(CommunityGroupDO communityGroupDO){
        PosterQuery posterQuery = new PosterQuery();
        posterQuery.setProfilePhoto(communityGroupDO.getGroupAvatarUrl());
        posterQuery.setTitle(communityGroupDO.getGroupName()+"社区群");
        posterQuery.setTag(communityGroupDO.getCommunityName());
        posterQuery.setBrief(communityGroupDO.getGroupBrief());
        posterQuery.setAddress(communityGroupDO.getGroupAddrDetail());
        posterQuery.setPosterUrl(communityGroupDO.getPosterUrl());
        return posterQuery;
    }
}
