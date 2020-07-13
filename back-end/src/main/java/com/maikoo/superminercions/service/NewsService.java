package com.maikoo.superminercions.service;

import com.maikoo.superminercions.model.dto.NewsBackDTO;
import com.maikoo.superminercions.model.dto.NewsDTO;
import com.maikoo.superminercions.model.dto.NewsInformationDTO;
import com.maikoo.superminercions.model.query.NewsQuery;

import java.util.List;

public interface NewsService {

    NewsInformationDTO information(long id);

    List<NewsDTO> list(int pageNumber);

    List<NewsBackDTO> getNewList();

    void removeNews(int newsId);

    void addNews(NewsQuery newsQuery);

    void updateNewsStatus(int newsId,int status);

}
