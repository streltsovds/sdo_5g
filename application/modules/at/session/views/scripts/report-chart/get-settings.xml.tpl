<settings>
  <data_type>xml</data_type>
  <font>Tahoma</font>
  <text_size>11</text_size>
  <depth>0</depth>
  <angle>30</angle>
  <thousands_separator>,</thousands_separator>

  <column>
    <width>75</width>
    <spacing>2</spacing>
    <grow_time>1</grow_time>

    <grow_effect>elastic</grow_effect>
    <balloon_text>
      <![CDATA[{value}]]>
    </balloon_text>
  </column>

  <plot_area>
    <margins>
      <top>40</top>
      <left>60</left>
      <bottom>40</bottom>
      <right>40</right>

    </margins>
  </plot_area>

  <grid>
    <category>
      <alpha>10</alpha>
      <dashed>true</dashed>
    </category>
    <value>
      <alpha>10</alpha>
      <dashed>true</dashed>

    </value>
  </grid>

  <values>
    <category>
      <color>999999</color>
    </category>
    <value>
      <min>0</min>
    </value>
  </values>


  <axes>
    <category>
      <color>E7E7E7</color>
      <width>1</width>
    </category>
    <value>
      <color>#E7E7E7</color>
      <width>1</width>
    </value>
  </axes>

  <balloon>
    <text_color>000000</text_color>
	<color>#D4B922</color>
    <corner_radius>4</corner_radius>
    <border_width>3</border_width>
    <border_alpha>50</border_alpha>
    <border_color>#000000</border_color>
    <alpha>80</alpha>
  </balloon>


  <labels>
    <label>
      <x>0</x>
      <y>7</y>
      <text_color>000000</text_color>
      <text_size>13</text_size>
      <align>center</align>
    </label>
  </labels>

  <legend>
    <enabled>1</enabled>
    <align></align>
    <border_alpha>100</border_alpha>
    <border_color>E7E7E7</border_color>
    <margins>5</margins>
  </legend>

  <graphs>
    <graph gid="analytics-user">
      <color><?php echo $this->palette[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_USER]?></color>
      <line_width>2</line_width>
      <balloon_text>
	      <![CDATA[Профиль пользователя по итогам текущей оценочной сессии: {value}]]>
      </balloon_text>
    </graph>
    <graph gid="analytics-profile">
      <color><?php echo $this->palette[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_PROFILE]?></color>
      <line_width>2</line_width>
      <balloon_text>
	      <![CDATA[Профиль успешности должности пользователя: {value}]]>
      </balloon_text>
    </graph>
    <graph gid="analytics-sessions">
      <color><?php echo $this->palette[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_SESSIONS]?></color>
      <line_width>2</line_width>
      <balloon_text>
	      <![CDATA[Профиль пользователя по итогам прошлой оценочной сессии: {value}]]>
      </balloon_text>
    </graph>
    <graph gid="analytics-position">
      <color><?php echo $this->palette[HM_At_Evaluation_Method_CompetenceModel::ANALYTICS_GRAPH_POSITION]?></color>
      <line_width>2</line_width>
      <balloon_text>
	      <![CDATA[Профиль успешности другой должности: {value}]]>
      </balloon_text>
    </graph>
  </graphs>

  <strings>
	  <no_data>Нет данных для отображения</no_data>
  </strings>

  </settings>