package com.maikoo.businessdirectory.model.view;

public interface GroupView {
    public static interface Public {
    }

    public static interface Class extends Public {
    }

    public static interface Community extends Public {
    }

    public static interface Country extends Public {
    }

    public static interface School extends Public {
    }

    public static interface SearchList {}

    public static interface Insert {}

    public static interface AdminPublic{}

    public static interface AdminClass extends AdminPublic {
    }

    public static interface AdminCommunity extends AdminPublic {
    }

    public static interface AdminCountry extends AdminPublic {
    }

    public static interface AdminSchool extends AdminPublic {
    }

    public static interface AdminInformation{}

    public static interface AdminClassInformation extends AdminInformation {
    }

    public static interface AdminCommunityInformation extends AdminInformation {
    }

    public static interface AdminCountryInformation extends AdminInformation {
    }

    public static interface AdminSchoolInformation extends AdminInformation {
    }
}
